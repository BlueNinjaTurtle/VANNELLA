#!/usr/bin/env python3
"""
🤖 Émulateur SBC-PT (Single Board Computer - Packet Tracer)
Simule les lectures RFID et envoie les requêtes HTTP à l'API VANNELLA
"""

import json
import requests
import time
import sys
from datetime import datetime
from pathlib import Path

# Couleurs pour le terminal
class Colors:
    HEADER = '\033[95m'
    BLUE = '\033[94m'
    CYAN = '\033[96m'
    GREEN = '\033[92m'
    YELLOW = '\033[93m'
    RED = '\033[91m'
    RESET = '\033[0m'
    BOLD = '\033[1m'

def load_config():
    """Charger la configuration"""
    config_path = Path(__file__).parent / 'config_iot_rfid.json'
    if not config_path.exists():
        print(f"{Colors.RED}❌ Fichier de config manquant: {config_path}{Colors.RESET}")
        sys.exit(1)

    with open(config_path, 'r', encoding='utf-8') as f:
        return json.load(f)

def print_banner():
    """Afficher le banneau de démarrage"""
    print(f"""
{Colors.BOLD}{Colors.CYAN}
╔════════════════════════════════════════════════════════════════╗
║                  🤖 ÉMULATEUR SBC-PT (RFID)                    ║
║            Simulation des Lecteurs RFID - Cisco PT             ║
║                     VANNELLA IoT Bridge                         ║
╚════════════════════════════════════════════════════════════════╝
{Colors.RESET}
    """)

def log_message(message, level='INFO'):
    """Logger un message avec timestamp"""
    timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    if level == 'INFO':
        print(f"{Colors.BLUE}[{timestamp}] ℹ️  {message}{Colors.RESET}")
    elif level == 'SUCCESS':
        print(f"{Colors.GREEN}[{timestamp}] ✅ {message}{Colors.RESET}")
    elif level == 'WARNING':
        print(f"{Colors.YELLOW}[{timestamp}] ⚠️  {message}{Colors.RESET}")
    elif level == 'ERROR':
        print(f"{Colors.RED}[{timestamp}] ❌ {message}{Colors.RESET}")

def send_rfid_reading(api_url, id_carte, id_lecteur, nom_lecteur, id_salle):
    """
    Envoyer une lecture RFID à l'API
    Simule le SBC-PT qui envoie une requête HTTP POST
    """
    payload = {
        'id_carte': id_carte,
        'id_lecteur': id_lecteur,
        'nom_lecteur': nom_lecteur,
        'id_salle': id_salle
    }

    try:
        log_message(f"📡 Envoi lecture: {id_carte} → {nom_lecteur} (Salle #{id_salle})")

        response = requests.post(
            api_url,
            json=payload,
            timeout=5,
            headers={'Content-Type': 'application/json'}
        )

        if response.status_code == 200:
            data = response.json()
            if data.get('status') == 'success':
                log_message(
                    f"Carte '{id_carte}' lue dans {nom_lecteur} - Salle: {data['data'].get('nom_salle')} [{data['data'].get('etat_salle')}]",
                    'SUCCESS'
                )
                return True
            else:
                log_message(f"Réponse API: {data.get('message')}", 'WARNING')
                return False
        else:
            log_message(f"HTTP {response.status_code}: {response.text}", 'ERROR')
            return False

    except requests.exceptions.ConnectionError:
        log_message("Connexion refusée - API non accessible", 'ERROR')
        return False
    except Exception as e:
        log_message(f"Erreur requête: {str(e)}", 'ERROR')
        return False

def interactive_mode(config):
    """Mode interactif - simule les lectures manuellement"""
    api_url = config['api_base_url'] + config['api_endpoint']
    lecteurs = config['lecteurs_rfid']
    cartes = config['cartes_test']

    log_message("🎮 Mode INTERACTIF - Simuler les lectures RFID", 'INFO')
    print(f"\n{Colors.BOLD}Lecteurs disponibles:{Colors.RESET}")
    for i, lecteur in enumerate(lecteurs, 1):
        print(f"  {i}. {lecteur['nom_salle']} (ID: {lecteur['id_lecteur']})")

    print(f"\n{Colors.BOLD}Cartes disponibles:{Colors.RESET}")
    for i, carte in enumerate(cartes, 1):
        print(f"  {i}. {carte['id_carte']} - {carte['proprietaire']}")

    print(f"\n{Colors.BOLD}Commandes:{Colors.RESET}")
    print("  lecture <num_lecteur> <num_carte> - Simuler une lecture")
    print("  info                                - Afficher les infos")
    print("  exit                                - Quitter")

    while True:
        try:
            cmd = input(f"\n{Colors.CYAN}> {Colors.RESET}").strip().lower()

            if cmd == 'exit':
                log_message("Au revoir! 👋", 'INFO')
                break

            elif cmd == 'info':
                print(f"\n{Colors.BOLD}Configuration:${Colors.RESET}")
                print(f"  API: {api_url}")
                print(f"  Lecteurs: {len(lecteurs)}")
                print(f"  Cartes test: {len(cartes)}")

            elif cmd.startswith('lecture '):
                parts = cmd.split()
                if len(parts) != 3:
                    log_message("Usage: lecture <num_lecteur> <num_carte>", 'WARNING')
                    continue

                try:
                    num_lecteur = int(parts[1]) - 1
                    num_carte = int(parts[2]) - 1

                    if not (0 <= num_lecteur < len(lecteurs)):
                        log_message("Numéro de lecteur invalide", 'ERROR')
                        continue

                    if not (0 <= num_carte < len(cartes)):
                        log_message("Numéro de carte invalide", 'ERROR')
                        continue

                    lecteur = lecteurs[num_lecteur]
                    carte = cartes[num_carte]

                    send_rfid_reading(
                        api_url,
                        carte['id_carte'],
                        lecteur['id_lecteur'],
                        lecteur['nom_salle'],
                        lecteur['id_salle']
                    )

                except ValueError:
                    log_message("Les numéros doivent être des entiers", 'ERROR')

            else:
                log_message("Commande inconnue. Tapez 'exit' pour quitter.", 'WARNING')

        except KeyboardInterrupt:
            print()
            log_message("Interruption utilisateur", 'WARNING')
            break
        except Exception as e:
            log_message(f"Erreur: {str(e)}", 'ERROR')

def auto_mode(config, interval=3):
    """Mode automatique - simule des lectures périodiques"""
    api_url = config['api_base_url'] + config['api_endpoint']
    lecteurs = config['lecteurs_rfid']
    cartes = config['cartes_test']

    log_message("🤖 Mode AUTOMATIQUE - Lectures périodiques", 'INFO')
    log_message(f"Intervalle: {interval}s entre les lectures", 'INFO')
    log_message("Appuyez sur Ctrl+C pour arrêter...", 'INFO')

    counter = 0
    try:
        while True:
            counter += 1
            lecteur = lecteurs[counter % len(lecteurs)]
            carte = cartes[counter % len(cartes)]

            print(f"\n{Colors.BOLD}Lecture #{counter}{Colors.RESET}")

            send_rfid_reading(
                api_url,
                carte['id_carte'],
                lecteur['id_lecteur'],
                lecteur['nom_salle'],
                lecteur['id_salle']
            )

            time.sleep(interval)

    except KeyboardInterrupt:
        print()
        log_message("Arrêt demandé par l'utilisateur", 'INFO')

def main():
    """Fonction principale"""
    print_banner()

    # Charger la config
    config = load_config()
    log_message("✅ Configuration chargée", 'SUCCESS')

    # Vérifier l'API
    api_url = config['api_base_url'] + config['api_endpoint']
    log_message(f"API cible: {api_url}", 'INFO')

    # Menu de sélection du mode
    print(f"\n{Colors.BOLD}Sélectionner le mode:{Colors.RESET}")
    print("  1. Mode INTERACTIF (manuellement)")
    print("  2. Mode AUTOMATIQUE (périodique)")
    print("  3. Quitter")

    choice = input(f"\n{Colors.CYAN}Choix (1/2/3):{Colors.RESET} ").strip()

    if choice == '1':
        interactive_mode(config)
    elif choice == '2':
        auto_mode(config, interval=3)
    else:
        log_message("Au revoir! 👋", 'INFO')

if __name__ == '__main__':
    main()
