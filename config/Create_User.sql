CREATE TABLE IF NOT EXISTS `User` (
  UserID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  Type ENUM('internal','contact','system') NOT NULL DEFAULT 'internal',
  Email VARCHAR(255) NOT NULL,
  FirstName VARCHAR(50) NULL,
  LastName VARCHAR(50) NULL,
  Position VARCHAR(100) NULL,
  Notes mediumtext,
  StreetAddress VARCHAR(255) NULL,
  ShippingAddress VARCHAR(255) NULL,
  City VARCHAR(50) NULL,
  State VARCHAR(2) NULL,
  Zip VARCHAR(9) NOT NULL DEFAULT '000000000',
  Country VARCHAR(50) NULL,
  WorkPhone VARCHAR(25) NULL,
  HomePhone VARCHAR(25) NULL,
  CellPhone VARCHAR(25) NULL,
  Active TINYINT(1) NOT NULL DEFAULT '1',
  Visible TINYINT(1) NOT NULL DEFAULT '1',
  Password VARCHAR(255) BINARY NOT NULL DEFAULT '',
  PRIMARY KEY (UserID),
  UNIQUE INDEX Unique_Email (Email)
);

INSERT INTO User (Email, FirstName, LastName, Visible) VALUES ('MasterAdmin', 'Master', 'Admin', 0);
INSERT INTO User_Key (UserID, KeyID, `Level`) VALUES ((SELECT u.UserID FROM `User` u WHERE u.Email = 'MasterAdmin'), (SELECT r.KeyID FROM ResourceKey r WHERE r.Name = 'Admin'), 1000);
