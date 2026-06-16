# 🤖 Guide Cisco Packet Tracer - Simulation RFID VANNELLA

## 📋 Table des matières
1. [Vue d'ensemble](#vue-densemble)
2. [Architecture réseau](#architecture-réseau)
3. [Instructions de construction](#instructions-de-construction)
4. [Configuration du SBC-PT](#configuration-du-sbc-pt)
5. [Lancement de la simulation](#lancement-de-la-simulation)
6. [Dépannage](#dépannage)

---

## 🎯 Vue d'ensemble

Ce projet simule un **système IoT RFID complet** avec:
- **5 lecteurs RFID** (un par salle)
- **1 SBC-PT** (Single Board Computer) qui collecte les données
- **API HTTP** qui reçoit les requêtes du SBC
- **Base de données** pour tracer les lectures

**Flux:**
```
Lecteur RFID 1 ┐
Lecteur RFID 2 ├─> SBC-PT --HTTP--> API PHP ---> Base de Données
Lecteur RFID 3 ├─> (passe-plat)
Lecteur RFID 4 ┤
Lecteur RFID 5 ┘
```

---

## 🏗️ Architecture réseau

### Topologie
```
┌─────────────────────────────────────────────────────┐
│           Réseau IoT RFID 192.168.1.0/24            │
├─────────────────────────────────────────────────────┤
│                                                     │
│  LECTEUR 1        LECTEUR 2        LECTEUR 3       │
│  192.168.1.10     192.168.1.11     192.168.1.12    │
│  (GRANDE SALLE)   (CISCO)          (UPL)           │
│        │                 │                 │       │
│        └─────────────────┼─────────────────┘       │
│                          │                         │
│        ┌─────────────────┼─────────────────┐       │
│        │                 │                 │       │
│  LECTEUR 4        LECTEUR 5              SWITCH    │
│  192.168.1.13     192.168.1.14       (Hub)        │
│  (PALANGUI)    (ANCIENNE PREP)                    │
│        │                 │                 │       │
│        └─────────────────┼─────────────────┘       │
│                          │                         │
│                       SBC-PT                       │
│                   192.168.1.50                     │
│            (Collecteur de données)                 │
│                          │                         │
│        ┌─────────────────┘                         │
│        │                                           │
│    Internet (vers API)                           │
│   (http://localhost/VANNELLA/api/...)            │
│                                                     │
└─────────────────────────────────────────────────────┘
```

### Composants
| Composant | Modèle | Nombre | Rôle |
|-----------|--------|--------|------|
| Lecteur RFID | IoT Device | 5 | Lire les cartes |
| SBC | IoT Device (PT-Host) | 1 | Collecter et traiter |
| Switch | 2960 Catalyst | 1 | Connecter tous les appareils |
| Routeur (optionnel) | 2900 | 1 | Accès Internet simulé |

---

## 🛠️ Instructions de construction

### Étape 1: Démarrer Cisco Packet Tracer
1. Ouvrir **Cisco Packet Tracer**
2. Créer un **nouveau projet**
3. Aller en mode **Realtime** (en bas à droite)

### Étape 2: Ajouter le Switch
1. **Device → Switches**
2. Sélectionner **Switch 2960**
3. Placer dans le canvas

### Étape 3: Ajouter les Lecteurs RFID
Pour chaque lecteur (x5):
1. **Device → IoT Devices** (ou End Devices)
2. Sélectionner **IoT Device** ou **Thing**
3. Placer 5 appareils dans le canvas
4. Nommer: `Lecteur_GRANDE_SALLE`, `Lecteur_CISCO`, etc.

### Étape 4: Ajouter le SBC-PT
1. **Device → IoT Devices**
2. Sélectionner **PT-Host** (ou IoT Gateway/SBC)
3. Placer au centre
4. Nommer: `SBC-PT-Collecteur`

### Étape 5: Connecter les appareils
1. **Connection → Copper Straight-Through**
2. Connecter chaque **Lecteur RFID → Switch**
3. Connecter **SBC-PT → Switch**

```
Lecteur 1 ─┐
Lecteur 2 ─┼─ Switch ─ SBC-PT
Lecteur 3 ─┤
Lecteur 4 ─┤
Lecteur 5 ─┘
```

### Étape 6: Configurer les adresses IP

#### Pour chaque Lecteur RFID:
1. **Clic droit sur le lecteur → Configuration**
2. Aller dans **Interface → FastEthernet0**
3. Configurer **IP statique**:
   - Lecteur 1: `192.168.1.10`
   - Lecteur 2: `192.168.1.11`
   - Lecteur 3: `192.168.1.12`
   - Lecteur 4: `192.168.1.13`
   - Lecteur 5: `192.168.1.14`
   - Masque: `255.255.255.0`
   - Gateway: `192.168.1.1`

#### Pour le SBC-PT:
1. **Clic droit → Configuration**
2. **Interface → FastEthernet0**
3. Configurer:
   - IP: `192.168.1.50`
   - Masque: `255.255.255.0`
   - Gateway: `192.168.1.1`

#### Pour le Switch:
1. **Clic droit → Configuration**
2. **Interface → VLAN1**
3. Configurer:
   - IP: `192.168.1.1`
   - Masque: `255.255.255.0`
   - (Le switch agit comme gateway)

---

## ⚙️ Configuration du SBC-PT

### Programmer le SBC-PT
1. **Clic droit sur le SBC → Open → IoT Device**
2. Aller dans **Scripting**

### Script Python sur le SBC-PT

Le SBC-PT doit exécuter un **script de collecte**:

```python
# Script du SBC-PT
# Collecte les données des lecteurs et les envoie à l'API

import requests
import json
import time

# Configuration
API_URL = "http://localhost/VANNELLA/api/iot_rfid_update.php"
LECTEURS = {
    "192.168.1.10": {"id": "LECTEUR_001", "nom": "GRANDE SALLE", "id_salle": 1},
    "192.168.1.11": {"id": "LECTEUR_002", "nom": "CISCO", "id_salle": 2},
    "192.168.1.12": {"id": "LECTEUR_003", "nom": "UPL", "id_salle": 3},
    "192.168.1.13": {"id": "LECTEUR_004", "nom": "PALANGUI", "id_salle": 4},
    "192.168.1.14": {"id": "LECTEUR_005", "nom": "ANCIENNE PREP", "id_salle": 5}
}

def send_reading(id_carte, id_lecteur, nom_lecteur, id_salle):
    """Envoyer une lecture à l'API"""
    payload = {
        "id_carte": id_carte,
        "id_lecteur": id_lecteur,
        "nom_lecteur": nom_lecteur,
        "id_salle": id_salle
    }
    try:
        response = requests.post(API_URL, json=payload, timeout=5)
        print(f"✅ Lecture envoyée: {id_carte} → {nom_lecteur}")
    except Exception as e:
        print(f"❌ Erreur: {str(e)}")

# Boucle principale
while True:
    # Recevoir les données des lecteurs (simulation)
    time.sleep(3)
```

---

## 🚀 Lancement de la simulation

### Configuration nécessaire sur votre PC

#### 1. Préparer la base de données
```bash
# Importer les tables SQL
mysql -u root -p gestion_salles < database_iot_rfid.sql
```

#### 2. Lancer le serveur PHP
```bash
# Démarrer XAMPP
# Ou en ligne de commande
php -S localhost:80 -t C:\xampp\htdocs\VANNELLA_PROJET
```

#### 3. Lancer l'émulateur Python
```bash
# Depuis le répertoire du projet
python iot_sbc_emulator.py
```

Vous verrez un menu:
```
Sélectionner le mode:
  1. Mode INTERACTIF (manuellement)
  2. Mode AUTOMATIQUE (périodique)
  3. Quitter

Choix (1/2/3):
```

#### 4. En mode INTERACTIF:
```
> lecture 1 1       # Lecteur 1, Carte 1
> lecture 2 2       # Lecteur 2, Carte 2
> lecture 3 3       # Lecteur 3, Carte 3
```

#### 5. Visualiser les données
- **Page web**: http://localhost/VANNELLA/simulation-iot-rfid.php
- **Page originale**: http://localhost/VANNELLA/simulation-iot.php

---

## 🧪 Dépannage

### Problème: "API non accessible"
```
Solution:
- Vérifier que Apache/PHP est en cours d'exécution
- Vérifier l'adresse API dans config_iot_rfid.json
- S'assurer que les tables SQL sont créées
```

### Problème: "Les lecteurs ne se connectent pas"
```
Solution:
- Vérifier les adresses IP (192.168.1.x)
- S'assurer que le Switch a l'IP 192.168.1.1
- Tester la connectivité: Ping entre appareils
```

### Problème: "Les données n'arrivent pas en BD"
```
Solution:
- Vérifier que config_iot_rfid.json est correct
- Vérifier les logs d'Apache
- Tester manuellement avec curl:
  curl -X POST http://localhost/VANNELLA/api/iot_rfid_update.php \
    -H "Content-Type: application/json" \
    -d '{"id_carte":"TEST","id_lecteur":"L1","nom_lecteur":"SALLE1","id_salle":1}'
```

### Problème: "Python ne se connecte pas à l'API"
```
Solution:
- S'assurer que l'API est accessible localement
- Vérifier la config: config_iot_rfid.json
- Tester manuellement: python iot_sbc_emulator.py
```

---

## 📊 Points clés de la simulation

| Aspect | Description |
|--------|-------------|
| **Lecteurs RFID** | Simulent des capteurs qui détectent les cartes |
| **SBC-PT** | Collecteur central qui traite les données |
| **API HTTP** | Reçoit les requêtes POST du SBC |
| **Mise à jour BD** | L'état des salles devient "OCCUPÉE" à la lecture |
| **Traçabilité** | Chaque lecture est enregistrée dans l'historique |

---

## 🎮 Modes d'utilisation

### Mode 1: Manuel (Interactif)
Parfait pour tester chaque lecture individuellement.

### Mode 2: Automatique (Périodique)
Simule des lectures continues (comme un flux réel).

### Mode 3: Cisco Packet Tracer
Construction du réseau complète (plus réaliste).

---

## 📝 Fichiers créés

```
C:\xampp\htdocs\VANNELLA_PROJET\
├── config_iot_rfid.json              # Configuration (lecteurs, cartes)
├── database_iot_rfid.sql             # Tables SQL (à importer)
├── iot_sbc_emulator.py               # Émulateur SBC-PT
├── simulation-iot-rfid.php           # Page web de visualisation
├── api/
│   ├── iot_rfid_update.php           # API réception des lectures
│   ├── get_lecteurs_rfid.php         # API liste des lecteurs
│   └── get_lectures_rfid.php         # API historique des lectures
└── CISCO_PACKET_TRACER_README.md     # Ce fichier
```

---

## ✅ Checklist de vérification

- [ ] Cisco Packet Tracer installé
- [ ] Réseau construit avec 5 lecteurs + 1 SBC
- [ ] Adresses IP configurées (192.168.1.x)
- [ ] Base de données créée (`database_iot_rfid.sql` importé)
- [ ] XAMPP/Apache en cours d'exécution
- [ ] Python 3 installé (avec `requests`)
- [ ] Fichier `config_iot_rfid.json` au bon endroit
- [ ] Page web accessible: http://localhost/VANNELLA/simulation-iot-rfid.php
- [ ] Émulateur Python lancé: `python iot_sbc_emulator.py`
- [ ] Mode interactif testé avec `lecture 1 1`

---

## 🚀 Prochaines étapes

1. **Construire le réseau** dans Cisco PT (étapes 1-6)
2. **Importer les tables SQL**
3. **Lancer l'émulateur Python**
4. **Tester les modes interactif/automatique**
5. **Visualiser les données** sur la page web

Bon succès! 🎯
