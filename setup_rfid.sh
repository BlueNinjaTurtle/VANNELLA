#!/bin/bash
# Script de configuration rapide - Installer les tables RFID

echo "================================================"
echo "  🚀 Configuration Automatique - VANNELLA IoT"
echo "================================================"
echo ""

# Récupérer les informations de connexion MySQL
read -p "🔐 Utilisateur MySQL (défaut: root): " MYSQL_USER
MYSQL_USER=${MYSQL_USER:-root}

read -sp "🔐 Mot de passe MySQL: " MYSQL_PASS
echo ""

read -p "📊 Base de données (défaut: gestion_salles): " MYSQL_DB
MYSQL_DB=${MYSQL_DB:-gestion_salles}

echo ""
echo "Connexion à: $MYSQL_DB comme $MYSQL_USER"
echo ""

# Importer les tables SQL
echo "📦 Installation des tables RFID..."
mysql -u "$MYSQL_USER" -p"$MYSQL_PASS" "$MYSQL_DB" < database_iot_rfid.sql

if [ $? -eq 0 ]; then
    echo "✅ Tables RFID créées avec succès!"
else
    echo "❌ Erreur lors de l'import SQL"
    exit 1
fi

echo ""
echo "================================================"
echo "  ✅ Configuration terminée!"
echo "================================================"
echo ""
echo "🎯 Prochaines étapes:"
echo "  1. Construire le réseau Cisco Packet Tracer"
echo "  2. Lancer l'émulateur: python iot_sbc_emulator.py"
echo "  3. Consulter: CISCO_PACKET_TRACER_README.md"
echo ""
