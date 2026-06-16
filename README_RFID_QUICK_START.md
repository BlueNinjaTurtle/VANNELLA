# 🎯 VANNELLA - Partie IoT RFID (Cisco Packet Tracer)

## 📖 Vue d'ensemble rapide

Bienvenue dans la nouvelle partie IoT RFID du projet VANNELLA! Cette architecture remplace la simulation Arduino/Proteus par un **vrai système IoT distribué** avec:

✅ **5 lecteurs RFID** (un par salle)  
✅ **1 SBC-PT** (Single Board Computer) collecteur  
✅ **API HTTP** pour la communication  
✅ **Base de données** pour la traçabilité  

---

## 🚀 Démarrage rapide (5 min)

### 1️⃣ Installer les tables SQL
```bash
# Exécuter le script d'installation
bash setup_rfid.sh

# Ou importer manuellement
mysql -u root -p gestion_salles < database_iot_rfid.sql
```

### 2️⃣ Vérifier la configuration
```bash
# S'assurer que config_iot_rfid.json est correct
cat config_iot_rfid.json
```

### 3️⃣ Lancer l'émulateur Python
```bash
python iot_sbc_emulator.py
```

### 4️⃣ Tester une lecture
```
Menu: Sélectionner 1 (Mode INTERACTIF)
> lecture 1 1
```

### 5️⃣ Visualiser les données
Ouvrir dans le navigateur:
- **Page RFID**: http://localhost/VANNELLA/simulation-iot-rfid.php
- **Page manuelle**: http://localhost/VANNELLA/simulation-iot.php

---

## 📁 Architecture des fichiers

```
VANNELLA_PROJET/
├── 📄 config_iot_rfid.json              ⚙️ Configuration (lecteurs, cartes)
├── 📄 database_iot_rfid.sql             📊 Tables SQL à importer
├── 🐍 iot_sbc_emulator.py               🤖 Émulateur SBC-PT
├── 📄 simulation-iot-rfid.php           🌐 Page web de visualisation
├── 📄 CISCO_PACKET_TRACER_README.md     📋 Guide complet Cisco PT
├── 📄 setup_rfid.sh                     🔧 Script installation
├── api/
│   ├── iot_rfid_update.php              📡 API réception (POST)
│   ├── get_lecteurs_rfid.php            📡 API lecteurs (GET)
│   └── get_lectures_rfid.php            📡 API historique (GET)
└── logs/
    └── iot_rfid.log                     📝 Logs de l'émulateur
```

---

## 🏗️ Architecture système

```
Cisco Packet Tracer (Réseau RFID)
    ↓
Lecteurs RFID × 5 (192.168.1.10-14)
    ↓
SBC-PT (192.168.1.50)
    ↓
Python Emulator (iot_sbc_emulator.py)
    ↓
HTTP POST → API PHP (iot_rfid_update.php)
    ↓
Base de Données (lectures_rfid, lecteurs_rfid)
    ↓
Page Web (simulation-iot-rfid.php)
```

---

## 🔌 Configuration

### config_iot_rfid.json
```json
{
  "api_base_url": "http://localhost/VANNELLA/api/",
  "api_endpoint": "iot_rfid_update.php",
  "lecteurs_rfid": [
    {
      "id_lecteur": "LECTEUR_001",
      "nom_salle": "GRANDE SALLE",
      "id_salle": 1
    },
    // ... 4 autres lecteurs
  ],
  "cartes_test": [
    {
      "id_carte": "CARD_001",
      "proprietaire": "Étudiant 1"
    }
    // ... autres cartes
  ]
}
```

---

## 🎮 Modes d'utilisation

### Mode 1: Interactif (Manuel)
Parfait pour tester chaque lecture:
```
> lecture 1 1      # Lecteur 1 (GRANDE SALLE), Carte 1
> lecture 2 2      # Lecteur 2 (CISCO), Carte 2
```

### Mode 2: Automatique (Périodique)
Simule un flux continu de lectures (3 sec d'intervalle):
```
Lecture #1: CARD_001 → GRANDE SALLE
Lecture #2: CARD_002 → CISCO
Lecture #3: CARD_003 → UPL
...
```

### Mode 3: Cisco Packet Tracer
Construction du réseau complet (voir guide détaillé).

---

## 📡 API REST

### POST `/api/iot_rfid_update.php`
**Reçoit les lectures RFID et met à jour la BD**

Request:
```json
{
  "id_carte": "CARD_001",
  "id_lecteur": "LECTEUR_001",
  "nom_lecteur": "GRANDE SALLE",
  "id_salle": 1
}
```

Response:
```json
{
  "status": "success",
  "message": "Lecture RFID enregistrée",
  "data": {
    "id_lecture": 42,
    "id_carte": "CARD_001",
    "nom_salle": "GRANDE SALLE",
    "etat_salle": "occupée",
    "timestamp": "2026-06-15 14:30:45"
  }
}
```

### GET `/api/get_lecteurs_rfid.php`
**Récupère les lecteurs RFID configurés**

Response:
```json
{
  "status": "success",
  "data": {
    "lecteurs": [...],
    "total_lecteurs": 5,
    "total_cartes": 8
  }
}
```

### GET `/api/get_lectures_rfid.php?limit=20`
**Récupère l'historique des lectures**

Response:
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "id_carte": "CARD_001",
      "id_lecteur": "LECTEUR_001",
      "nom_lecteur": "GRANDE SALLE",
      "id_salle": 1,
      "timestamp": "2026-06-15 14:30:45"
    },
    ...
  ]
}
```

---

## 🗄️ Tables de base de données

### `lecteurs_rfid`
```sql
- id_lecteur (VARCHAR) - ID unique
- id_salle (INT) - Référence à la salle
- nom_salle (VARCHAR) - Nom de la salle
- localisation (VARCHAR) - Position du lecteur
- actif (BOOLEAN) - Statut du lecteur
```

### `cartes_rfid`
```sql
- id_carte (VARCHAR) - ID unique de la carte
- proprietaire (VARCHAR) - Personne/Étudiant
- type_carte (ENUM) - etudiant/professeur/admin/test
- actif (BOOLEAN) - Statut de la carte
```

### `lectures_rfid`
```sql
- id_carte (VARCHAR) - Carte lue
- id_lecteur (VARCHAR) - Lecteur qui a lu
- nom_lecteur (VARCHAR) - Nom du lecteur
- id_salle (INT) - Salle où ça s'est passé
- timestamp (DATETIME) - Quand
```

---

## 🧪 Test rapide

### Test 1: Vérifier la configuration
```bash
cat config_iot_rfid.json | grep api_base_url
```

### Test 2: Tester l'API directement
```bash
curl -X POST http://localhost/VANNELLA/api/iot_rfid_update.php \
  -H "Content-Type: application/json" \
  -d '{
    "id_carte": "TEST_001",
    "id_lecteur": "LECTEUR_001",
    "nom_lecteur": "GRANDE SALLE",
    "id_salle": 1
  }'
```

Résultat attendu:
```json
{"status":"success","message":"Lecture RFID enregistrée",...}
```

### Test 3: Vérifier les lectures en BD
```bash
mysql -u root -p gestion_salles -e "SELECT * FROM lectures_rfid LIMIT 5;"
```

---

## 🔧 Dépannage

| Problème | Solution |
|----------|----------|
| "API non accessible" | Vérifier Apache/PHP + config_iot_rfid.json |
| "Tables non trouvées" | Importer: `mysql ... < database_iot_rfid.sql` |
| "Python erreur de connexion" | Vérifier api_base_url dans la config |
| "Aucune lecture affichée" | Rafraîchir le navigateur ou attendre 3 sec |
| "Python ne s'exécute pas" | Installer: `pip install requests` |

---

## 📚 Documentation complémentaire

- **Guide Cisco PT complet**: Voir `CISCO_PACKET_TRACER_README.md`
- **Configuration avancée**: Éditer `config_iot_rfid.json`
- **Ajouter des cartes**: INSERT dans `cartes_rfid`
- **Ajouter des lecteurs**: INSERT dans `lecteurs_rfid`

---

## ✅ Checklist

- [ ] `database_iot_rfid.sql` importé
- [ ] `config_iot_rfid.json` au bon endroit
- [ ] Apache/PHP en cours d'exécution
- [ ] Python 3 + requests installé
- [ ] `iot_sbc_emulator.py` peut s'exécuter
- [ ] Page web accessible
- [ ] Test manuel réussi (lecture 1 1)

---

## 🎯 Prochaines étapes

### Court terme
1. Tester l'émulateur en mode interactif
2. Vérifier les données en base de données
3. Consulter la page web de visualisation

### Moyen terme
4. Construire le réseau complet dans Cisco PT
5. Intégrer le SBC-PT personnalisé
6. Ajouter d'autres capteurs (température, luminosité)

### Long terme
7. Connexion en réseau réel
8. Déploiement sur Raspberry Pi
9. Interface de gestion avancée

---

## 💡 Points clés

✅ **Système distribué**: 5 lecteurs indépendants  
✅ **Communication HTTP**: Aucune connexion série  
✅ **Traçabilité**: Chaque lecture enregistrée  
✅ **Mise à jour en temps réel**: BD + page web  
✅ **Testable localement**: Sans Cisco PT d'abord  
✅ **Évolutif**: Facile d'ajouter des lecteurs  

---

## 📞 Support

En cas de problème:
1. Vérifier les logs d'Apache
2. Consulter le guide dépannage ci-dessus
3. Voir `CISCO_PACKET_TRACER_README.md`
4. Exécuter les tests rapides

---

## 📝 Licence

Ce projet fait partie de **VANNELLA** - ISPT Likasi 2026

---

**Bon succès! 🚀** Contacte-moi si tu as besoin d'aide.
