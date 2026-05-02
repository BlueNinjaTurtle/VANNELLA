#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Bridge IoT pour VANNELLA_PROJET
Relie les Arduino Proteus → Python → API PHP/MySQL
Version: 2.0 - Intégration complète

Fonction: Écouter le port série, valider les messages Arduino,
          mapper les noms de salles, et envoyer à l'API PHP
"""

import serial
import serial.tools.list_ports
import requests
import logging
import json
import time
from datetime import datetime
from pathlib import Path

# ============================================================================
# CONFIGURATION
# ============================================================================

# Configuration du mapping : Nom Arduino → ID DB
ROOM_TO_ID = {
    'GRANDE SALLE': 1,
    'CISCO': 2,
    'UPL': 3,
    'PALANGUI': 4,
    'ANCIENNE PREPARATOIRE': 5
}

# États autorisés (Arduino envoie en majuscules)
VALID_STATES = ['OCCUPEE', 'LIBRE']

# Créer automatiquement le dossier de logs à côté du script
BASE_DIR = Path(__file__).resolve().parent
LOG_DIR = BASE_DIR / 'logs'
LOG_DIR.mkdir(parents=True, exist_ok=True)
LOG_FILE = LOG_DIR / 'iot_bridge.log'

# Configuration du logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - [%(levelname)s] - %(message)s',
    handlers=[
        logging.FileHandler(LOG_FILE, encoding='utf-8'),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger(__name__)

# Configuration API
API_BASE_URL = "http://localhost/VANNELLA/api/"
API_ENDPOINT = "iot_update.php"
API_TIMEOUT = 5

# Configuration série
# Avec la paire virtuelle com0com COM3/COM4:
# - Proteus envoie sur COM3
# - Python écoute sur COM4
COM_PORT = 'COM4'
BAUDRATE = 9600
SERIAL_TIMEOUT = 1

# ============================================================================
# FONCTION : Détecter Arduino automatiquement
# ============================================================================

def find_arduino_port():
    """Détecte automatiquement le port COM de l'Arduino"""
    ports = list(serial.tools.list_ports.comports())

    if not ports:
        logger.warning("⚠ Aucun port série détecté. Vérifiez la connexion de l'Arduino.")
        return None

    available_ports = [port.device for port in ports]

    for port in ports:
        # Chercher des identifiants Arduino/CH340 courants
        if any(x in port.description.upper() for x in ['ARDUINO', 'CH340', 'CP2102', 'FT232']):
            logger.info(f"✓ Arduino détecté sur {port.device}")
            return port.device

    if COM_PORT in available_ports:
        logger.warning(f"⚠ Arduino non détecté automatiquement, utilisation du port configuré {COM_PORT}")
        return COM_PORT

    logger.warning(
        f"⚠ Arduino non détecté. Ports disponibles: {available_ports}. "
        f"Le port configuré {COM_PORT} est introuvable."
    )
    return None

# ============================================================================
# FONCTION : Parser le message Arduino
# ============================================================================

def parse_arduino_message(message):
    """
    Parse un message Arduino du format: ROOM=XXX;STATUS=YYY
    Retourne: (room_name, status) ou (None, None) si invalide
    """
    try:
        # Vérifier format de base
        if not message or "ROOM" not in message or "STATUS" not in message:
            logger.warning(f"⚠ Format invalide: {message}")
            return None, None
        
        # Split par point-virgule
        parts = message.split(";")
        if len(parts) < 2:
            logger.warning(f"⚠ Nombre de champs insuffisant: {message}")
            return None, None
        
        # Extraction ROOM
        room_part = parts[0].strip()
        if "=" not in room_part:
            logger.warning(f"⚠ Pas de '=' dans ROOM: {room_part}")
            return None, None
        room_name = room_part.split("=", 1)[1].strip()
        
        # Extraction STATUS
        status_part = parts[1].strip()
        if "=" not in status_part:
            logger.warning(f"⚠ Pas de '=' dans STATUS: {status_part}")
            return None, None
        status = status_part.split("=", 1)[1].strip().upper()
        
        return room_name, status
    
    except Exception as e:
        logger.error(f"✗ Erreur parsing: {e} | Message: {message}")
        return None, None

# ============================================================================
# FONCTION : Valider et normaliser les données
# ============================================================================

def validate_data(room_name, status):
    """
    Valide les données Arduino
    Retourne: (id_salle, etat_normalisé) ou (None, None) si invalide
    """
    # Valider le nom de salle
    if room_name not in ROOM_TO_ID:
        logger.warning(f"⚠ Salle inconnue: {room_name} (valides: {list(ROOM_TO_ID.keys())})")
        return None, None
    
    # Valider l'état
    if status not in VALID_STATES:
        logger.warning(f"⚠ État invalide: {status} (valides: {VALID_STATES})")
        return None, None
    
    # Lookup ID salle
    id_salle = ROOM_TO_ID[room_name]
    
    # Normaliser l'état : OCCUPEE → occupée, LIBRE → libre
    etat_normalized = 'occupée' if status == 'OCCUPEE' else 'libre'
    
    logger.info(f"✓ Validé: {room_name} (ID={id_salle}) → {etat_normalized}")
    return id_salle, etat_normalized

# ============================================================================
# FONCTION : Envoyer à l'API PHP
# ============================================================================

def send_to_api(id_salle, etat):
    """
    Envoie la mise à jour à l'API PHP
    Retourne: True si succès, False sinon
    """
    try:
        url = f"{API_BASE_URL}{API_ENDPOINT}?id_salle={id_salle}&etat={etat}"
        
        logger.info(f"→ Envoi HTTP: {url}")
        
        response = requests.get(url, timeout=API_TIMEOUT)
        
        if response.status_code == 200:
            try:
                result = response.json()
                if result.get('status') == 'OK':
                    logger.info(f"✓ API OK: {result.get('message', 'Mise à jour réussie')}")
                    return True
                else:
                    logger.error(f"✗ API Error: {result.get('message', 'Erreur inconnue')}")
                    return False
            except json.JSONDecodeError:
                logger.error(f"✗ Réponse JSON invalide: {response.text}")
                return False
        else:
            logger.error(f"✗ HTTP {response.status_code}: {response.text}")
            return False
    
    except requests.exceptions.Timeout:
        logger.error(f"✗ Timeout API (>{API_TIMEOUT}s)")
        return False
    
    except requests.exceptions.ConnectionError:
        logger.error(f"✗ Erreur connexion API (Apache non accessible?)")
        return False
    
    except Exception as e:
        logger.error(f"✗ Erreur API générale: {e}")
        return False

# ============================================================================
# FONCTION : Boucle principale
# ============================================================================

def main():
    """Boucle principale du bridge IoT"""
    
    logger.info("=" * 60)
    logger.info("🚀 Bridge IoT VANNELLA démarré")
    logger.info("=" * 60)
    logger.info(f"Configuration:")
    logger.info(f"  - Salles mappées: {list(ROOM_TO_ID.keys())}")
    logger.info(f"  - API: {API_BASE_URL}{API_ENDPOINT}")
    logger.info(f"  - Baudrate: {BAUDRATE}")
    
    ser = None
    port_current = None
    
    while True:
        try:
            # Initialiser/Réinitialiser la connexion série si fermée
            if ser is None or not ser.is_open:
                if ser is not None:
                    logger.warning("⚠ Reconnexion au port série...")
                
                port_to_use = find_arduino_port()
                if port_to_use is None:
                    time.sleep(5)
                    continue

                ser = serial.Serial(port_to_use, BAUDRATE, timeout=SERIAL_TIMEOUT)
                port_current = port_to_use
                
                logger.info(f"✓ Port série ouvert: {port_to_use}")
                time.sleep(1)  # Attendre que l'Arduino se réinitialise
            
            # Lire une ligne du port série
            if ser.in_waiting > 0:
                line = ser.readline().decode('utf-8', errors='ignore').strip()
                
                if line:
                    logger.info(f"← Reçu du port série: {line}")
                    
                    # Parser
                    room_name, status = parse_arduino_message(line)
                    
                    if room_name and status:
                        # Valider
                        id_salle, etat = validate_data(room_name, status)
                        
                        if id_salle and etat:
                            # Envoyer à l'API
                            success = send_to_api(id_salle, etat)
                            
                            if not success:
                                logger.warning(f"⚠ Mise à jour échouée pour {room_name}")
        
        except serial.SerialException as e:
            logger.error(f"✗ Erreur série sur {port_current or COM_PORT}: {e}")
            if ser is not None:
                ser.close()
            ser = None
            time.sleep(5)  # Attendre avant réessai
        
        except UnicodeDecodeError as e:
            logger.warning(f"⚠ Erreur décodage série: {e}")
            continue
        
        except Exception as e:
            logger.error(f"✗ Erreur générale: {e}", exc_info=True)
            time.sleep(2)
    
    # Cleanup (rarement atteint)
    if ser is not None:
        ser.close()
    logger.info("Bridge IoT arrêté")

# ============================================================================
# POINT D'ENTRÉE
# ============================================================================

if __name__ == '__main__':
    try:
        main()
    except KeyboardInterrupt:
        logger.info("\n⚠ Bridge IoT arrêté par l'utilisateur")
    except Exception as e:
        logger.critical(f"✗ Erreur critique: {e}", exc_info=True)
