# Digital Smile — Plateforme de gestion

Application web (client + employés + admin) pour l'agence **Digital Smile**.
Stack : **PHP + MySQL** (pur, sans framework), à lancer sur **XAMPP**.

---

## ✅ Phase actuelle : Phase 3 — Fondations & Sécurité

Ce paquet contient le **socle** du projet : structure, base de données,
connexion sécurisée, système multilingue, et pages de vérification.
Les interfaces visuelles viendront à la phase suivante.

---

## 🚀 Comment lancer le projet (5 étapes)

### 1. Copier le projet
Placez le dossier `digital-smile` dans le dossier `htdocs` de XAMPP :
```
C:\xampp\htdocs\digital-smile
```

### 2. Démarrer XAMPP
Ouvrez le **XAMPP Control Panel** et démarrez **Apache** et **MySQL**.

### 3. Créer la base de données
1. Allez sur http://localhost/phpmyadmin
2. Onglet **Importer**
3. Choisissez le fichier `database/schema.sql`
4. Cliquez **Exécuter**
→ La base `digital_smile` et ses 15 tables sont créées.

### 4. Vérifier que tout marche
Ouvrez dans le navigateur :
```
http://localhost/digital-smile/public/health.php
```
Vous devez voir une liste de **✅ OK**. Si oui, tout est prêt.

### 5. Créer le compte administrateur
Ouvrez **une seule fois** :
```
http://localhost/digital-smile/public/install.php
```
- Email : `admin@digitalsmile.dz`
- Mot de passe : `Admin@2026`

⚠️ **Ensuite, SUPPRIMEZ `install.php` et `health.php`** pour la sécurité.

---

## 📁 Structure du projet

```
digital-smile/
├── public/           <- SEUL dossier visible par le navigateur
│   ├── index.php     (à venir : point d'entrée unique)
│   ├── health.php    (test de santé — à supprimer après)
│   ├── install.php   (création admin — à supprimer après)
│   ├── assets/       (css, js, images, polices)
│   └── uploads/      (fichiers déposés par les clients)
├── app/              <- Cœur de l'application (protégé)
│   ├── Controllers/  (logique de chaque page)
│   ├── Models/       (accès aux tables)
│   ├── Views/        (affichage HTML)
│   ├── Core/         (moteur : Database, Model, Router...)
│   └── Middleware/   (gardiens de permissions)
├── config/           (configuration — hors du navigateur)
├── lang/             (traductions fr / ar / en)
└── database/
    └── schema.sql    (structure complète de la base)
```

---

## 🔒 Choix de sécurité déjà en place

- **PDO + requêtes préparées** : protection contre les injections SQL.
- **Mots de passe hachés** (bcrypt) : jamais stockés en clair.
- **Config hors du dossier public** : inaccessible depuis le web.
- **uploads/ non exécutable** : un fichier déposé ne peut pas être un virus actif.
- **Soft delete** : les données ne sont jamais vraiment effacées (traçabilité).
