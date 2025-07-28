CREATE TABLE clients (
    id_client INT AUTO_INCREMENT PRIMARY KEY,
    nom_complet VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    mot_de_passe VARCHAR(255),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admins_normaux (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    nom
    prenom	
    email VARCHAR(100),
    mot_de_passe VARCHAR(255),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admins_principaux (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    nom
    prenom
    email VARCHAR(100),
    mot_de_passe VARCHAR(255),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE types_produits (
    id_type INT AUTO_INCREMENT PRIMARY KEY,
    nom_type VARCHAR(100) UNIQUE
);

CREATE TABLE produits (
    id_produit INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    description TEXT,
    prix DECIMAL(10,2),
    stock INT,
    image
    id_type INT,
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_type) REFERENCES types_produits(id_type)
);

CREATE TABLE commandes (
    id_commande INT AUTO_INCREMENT PRIMARY KEY,
    id_client INT,
    total DECIMAL(10,2),
    date_commande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_client) REFERENCES clients(id_client)
);

CREATE TABLE details_commandes (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_commande INT,
    id_produit INT,
    quantite INT,
    prix DECIMAL(10,2),
    FOREIGN KEY (id_commande) REFERENCES commandes(id_commande),
    FOREIGN KEY (id_produit) REFERENCES produits(id_produit)
);