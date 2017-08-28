GRANT USAGE ON *.* TO 'omnisys'@'localhost' IDENTIFIED BY '40two';
GRANT ALL PRIVILEGES ON `omnisys`.* TO 'omnisys'@'localhost';

CREATE DATABASE IF NOT EXISTS `omnisys`;
USE omnisys;

CREATE TABLE IF NOT EXISTS Area (
  AreaID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  Name VARCHAR(40) NULL,
  DataTag VARCHAR(255) NULL,
  PRIMARY KEY(AreaID)
);

CREATE TABLE IF NOT EXISTS Area_Zip (
  AreaID INTEGER UNSIGNED NOT NULL,
  Zip INTEGER(9) UNSIGNED NOT NULL,
  Country VARCHAR(20) NULL
);

CREATE TABLE IF NOT EXISTS Article (
  FeatureID INTEGER UNSIGNED NOT NULL,
  Article TEXT NULL,
  PRIMARY KEY(FeatureID),
  FULLTEXT INDEX Fulltext_Article_Article(Article)
);

CREATE TABLE IF NOT EXISTS Customer (
  CustomerID int(10) unsigned NOT NULL AUTO_INCREMENT,
  Name VARCHAR(255) NOT NULL,
  StreetAddress VARCHAR(255) NULL,
  ShippingAddress VARCHAR(255) NULL,
  City VARCHAR(50) NULL,
  State VARCHAR(2) NULL,
  Zip INTEGER(9) UNSIGNED NOT NULL,
  Country VARCHAR(50) NULL,
  Phone VARCHAR(25) NULL,
  Fax varchar(25) DEFAULT NULL,
  Active tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (CustomerID)
);

CREATE TABLE IF NOT EXISTS Customer_User (
  CustomerID INTEGER UNSIGNED NOT NULL,
  UserID INTEGER UNSIGNED NOT NULL,
  Position VARCHAR(100) NULL,
  PRIMARY KEY (CustomerID,UserID)
);

CREATE TABLE IF NOT EXISTS Feature (
  FeatureID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  UserID INTEGER UNSIGNED NOT NULL,
  Name VARCHAR(255) NOT NULL,
  Title VARCHAR(100) NULL,
  Description VARCHAR(255) NULL,
  StartTime TIMESTAMP NULL,
  StopTime TIMESTAMP NULL,
  Type VARCHAR(100) NULL,
  LastUpdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CreateTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  DataTag VARCHAR(255) NULL,
  PRIMARY KEY(FeatureID)
);

CREATE TABLE IF NOT EXISTS FeaturePosition (
  FeatureID INTEGER UNSIGNED NOT NULL,
  PositionID INTEGER UNSIGNED NOT NULL,
  Weight INTEGER UNSIGNED DEFAULT 1,
  UNIQUE INDEX Unique_FeaturePosition(FeatureID, PositionID)
);

CREATE TABLE IF NOT EXISTS Gallery (
  FeatureID INTEGER UNSIGNED NOT NULL,
  ImageDir VARCHAR(255) NULL,
  ThumbWidth INTEGER UNSIGNED NULL,
  ThumbHeight INTEGER UNSIGNED NULL,
  ImageStyle ENUM('popup', 'inline') NOT NULL DEFAULT 'popup',
  PRIMARY KEY(FeatureID)
);

CREATE TABLE IF NOT EXISTS GalleryImage (
  ImageID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  FeatureID INTEGER UNSIGNED NOT NULL,
  FileName VARCHAR(45) NOT NULL,
  Title VARCHAR(100) NULL,
  Description VARCHAR(255) NULL,
  Sequence INTEGER UNSIGNED NULL,
  PRIMARY KEY (ImageID)
);

CREATE TABLE IF NOT EXISTS Poll (
  FeatureID INTEGER UNSIGNED NOT NULL,
  Question VARCHAR(255) NULL,
  PRIMARY KEY(FeatureID)
);

CREATE TABLE IF NOT EXISTS PollChoice (
  ChoiceID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  FeatureID INTEGER UNSIGNED NOT NULL,
  Choice VARCHAR(255) NULL,
  PRIMARY KEY(ChoiceID)
);

CREATE TABLE IF NOT EXISTS PollResponse (
  ChoiceID INTEGER UNSIGNED NOT NULL,
  FeatureID INTEGER UNSIGNED NOT NULL
);

CREATE TABLE IF NOT EXISTS Position (
  PositionID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  Name VARCHAR(255) NOT NULL,
  DataTag VARCHAR(255) NULL,
  PRIMARY KEY(PositionID),
  UNIQUE INDEX Unique_Position(Name)
);

CREATE TABLE IF NOT EXISTS ResourceKey (
  KeyID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  Name VARCHAR(25) NOT NULL,
  PRIMARY KEY(KeyID),
  UNIQUE INDEX Unique_ResourceName(Name)
);
INSERT INTO ResourceKey (Name) VALUES ('Admin');

CREATE TABLE IF NOT EXISTS ResourceLock (
  LockID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  KeyID INTEGER UNSIGNED NOT NULL,
  MinKey INTEGER UNSIGNED NOT NULL DEFAULT 1000,
  Resource VARCHAR(255) NULL,
  Component VARCHAR(255) NULL,
  PRIMARY KEY(LockID)
);

CREATE TABLE IF NOT EXISTS Review (
  FeatureID INTEGER UNSIGNED NOT NULL,
  Rating INTEGER UNSIGNED NULL,
  Genre VARCHAR(255) NULL,
  Review TEXT NULL,
  PRIMARY KEY(FeatureID),
  FULLTEXT INDEX Fulltext_Review_Review(Review)
);

CREATE TABLE IF NOT EXISTS Settings (
  Type VARCHAR(255) NOT NULL,
  Data TEXT NULL,
  PRIMARY KEY(Type)
);

CREATE TABLE IF NOT EXISTS Software (
  SoftwareID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  Name VARCHAR(255) NOT NULL,
  CVSName VARCHAR(255) NOT NULL,
  Description text,
  PRIMARY KEY (SoftwareID),
  UNIQUE INDEX Unique_SoftwareName (Name)
);

CREATE TABLE IF NOT EXISTS SoftwareElement (
  ElementID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  SoftwareID INTEGER UNSIGNED NOT NULL,
  Name VARCHAR(255) NOT NULL,
  UserID INTEGER UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (ElementID),
  UNIQUE INDEX Unique_SoftwareElement (SoftwareID,Name)
);

CREATE TABLE IF NOT EXISTS SoftwareRelease (
  ReleaseID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  SoftwareID int(11) unsigned NOT NULL,
  TicketID int(11) unsigned NOT NULL DEFAULT 0,
  Major INTEGER UNSIGNED NOT NULL DEFAULT 0,
  Minor INTEGER UNSIGNED NOT NULL DEFAULT 0,
  Patch INTEGER UNSIGNED NOT NULL DEFAULT 0,
  Note text,
  PRIMARY KEY (ReleaseID),
  UNIQUE INDEX Unique_SoftwareVersion (SoftwareID,Major,Minor,Patch),
  INDEX Index_Software (SoftwareID)
);

CREATE TABLE IF NOT EXISTS Template (
  TemplateID INTEGER UNSIGNED NOT NULL,
  Uri VARCHAR(255) NOT NULL,
  TemplateText TEXT NULL,
  PRIMARY KEY (TemplateID),
  UNIQUE INDEX Unique_Uri (Uri),
  FULLTEXT INDEX Fulltext_Template_TemplateText (TemplateText)
);

CREATE TABLE IF NOT EXISTS Ticket (
  TicketID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  OwnerID INTEGER UNSIGNED NOT NULL,
  CategoryID INTEGER UNSIGNED NOT NULL,
  ParentID INTEGER UNSIGNED NOT NULL DEFAULT 0,
  Type ENUM('internal','contact','system','software') NOT NULL DEFAULT 'internal',
  Subject VARCHAR(255) NOT NULL,
  TimeSpent INTEGER UNSIGNED NULL,
  CreateTime TIMESTAMP NULL,
  CreatorID INTEGER UNSIGNED NOT NULL,
  StartDate DATE NULL,
  LastUpdate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  DueDate DATE NULL,
  CompletionTime TIMESTAMP NULL,
  `Status` ENUM('new','active','pending','closed') NOT NULL DEFAULT 'new',
  Priority ENUM('low','normal','high','critical') NOT NULL DEFAULT 'normal',
  SoftwareID INTEGER UNSIGNED NOT NULL,
  ElementID INTEGER UNSIGNED NOT NULL,
  ReleaseID INTEGER UNSIGNED NOT NULL,
  Severity ENUM('wish list','feature','change','performance','minor bug','major bug','crash') NOT NULL DEFAULT 'feature',
  Projection ENUM('unknown','very minor','minor','average','major','very major','redesign') NOT NULL DEFAULT 'unknown',
  DevStatus ENUM('review','verified','unable to reproduce','not fixable','duplicate','no change required','won\'t fix','in progress','complete') NOT NULL DEFAULT 'review',
  QualityStatus ENUM('failed','passed','untested','retest','in progress','pending developer response') NOT NULL DEFAULT 'untested',
  Description text NULL,
  StepsToReproduce text NULL,
  PRIMARY KEY (TicketID),
  FULLTEXT INDEX Fulltext_Ticket_Description (Description),
  FULLTEXT INDEX Fulltext_Ticket_StepsToReproduce (StepsToReproduce)
);

CREATE TABLE IF NOT EXISTS TicketCategory (
  CategoryID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  Name VARCHAR(255) NOT NULL DEFAULT '',
  UserID INTEGER UNSIGNED NOT NULL DEFAULT 0,
  KeyID INTEGER UNSIGNED NOT NULL DEFAULT 0,
  Level INTEGER UNSIGNED NOT NULL DEFAULT 0,
  AssignmentMethod ENUM('leasttickets','unassigned','direct','roundrobin') NOT NULL DEFAULT 'unassigned',
  INDEX Index_CategoryName (Name),
  PRIMARY KEY (CategoryID)
);

CREATE TABLE IF NOT EXISTS TicketContent (
  ContentID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  TicketID INTEGER UNSIGNED NOT NULL DEFAULT 0,
  UserID INTEGER UNSIGNED NOT NULL DEFAULT 0,
  UpdateTime datetime DEFAULT NULL,
  UpdateText longtext,
  UpdateType ENUM('public','private','system') NOT NULL DEFAULT 'private',
  TimeWorked INTEGER UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (ContentID),
  INDEX Index_Ticket (TicketID),
  FULLTEXT INDEX Fulltext_TicketContent_UpdateText (UpdateText)
);

CREATE TABLE IF NOT EXISTS TicketHistory (
  HistoryID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  ContentID  INTEGER UNSIGNED NOT NULL DEFAULT 0,
  CarbonCopied VARCHAR(255) NULL,
  CarbonCopyFailed VARCHAR(255) NULL,
  UserIDFrom INTEGER UNSIGNED NOT NULL DEFAULT 0,
  UserIDTo INTEGER UNSIGNED NOT NULL DEFAULT 0,
  CategoryIDFrom INTEGER UNSIGNED NOT NULL DEFAULT 0,
  CategoryIDTo INTEGER UNSIGNED NOT NULL DEFAULT 0,
  ParentIDFrom INTEGER UNSIGNED NOT NULL DEFAULT 0,
  ParentIDTo INTEGER UNSIGNED NOT NULL DEFAULT 0,
  CustomerIDFrom INTEGER UNSIGNED NOT NULL DEFAULT 0,
  CustomerIDTo INTEGER UNSIGNED NOT NULL DEFAULT 0,
  TypeFrom ENUM('internal','contact','system','software') NOT NULL DEFAULT 'internal',
  TypeTo ENUM('internal','contact','system','software') NOT NULL DEFAULT 'internal',
  SubjectFrom VARCHAR(255) NOT NULL,
  SubjectTo VARCHAR(255) NOT NULL,
  StartDateFrom DATE NULL,
  StartDateTo DATE NULL,
  DueDateFrom DATE NULL,
  DueDateTo DATE NULL,
  StatusFrom ENUM('new','active','pending','closed') NOT NULL DEFAULT 'new',
  StatusTo ENUM('new','active','pending','closed') NOT NULL DEFAULT 'new',
  PriorityFrom ENUM('low','normal','high','critical') NOT NULL DEFAULT 'normal',
  PriorityTo ENUM('low','normal','high','critical') NOT NULL DEFAULT 'normal',
  SoftwareIDFrom INTEGER UNSIGNED NOT NULL DEFAULT 0,
  SoftwareIDTo INTEGER UNSIGNED NOT NULL DEFAULT 0,
  ElementIDFrom INTEGER UNSIGNED NOT NULL DEFAULT 0,
  ElementIDTo INTEGER UNSIGNED NOT NULL DEFAULT 0,
  ReleaseIDFrom INTEGER UNSIGNED NOT NULL DEFAULT 0,
  ReleaseIDTo INTEGER UNSIGNED NOT NULL DEFAULT 0,
  SeverityFrom ENUM('wish list','feature','change','performance','minor bug','major bug','crash') NOT NULL DEFAULT 'feature',
  SeverityTo ENUM('wish list','feature','change','performance','minor bug','major bug','crash') NOT NULL DEFAULT 'feature',
  ProjectionFrom ENUM('unknown','very minor','minor','average','major','very major','redesign') NOT NULL DEFAULT 'unknown',
  ProjectionTo ENUM('unknown','very minor','minor','average','major','very major','redesign') NOT NULL DEFAULT 'unknown',
  DevStatusFrom ENUM('review','verified','unable to reproduce','not fixable','duplicate','no change required','won\'t fix','in progress','fixed') NOT NULL DEFAULT 'review',
  DevStatusTo ENUM('review','verified','unable to reproduce','not fixable','duplicate','no change required','won\'t fix','in progress','fixed') NOT NULL DEFAULT 'review',
  QualityStatusFrom ENUM('failed','passed','untested','retest','in progress','pending developer response') NOT NULL DEFAULT 'untested',
  QualityStatusTo ENUM('failed','passed','untested','retest','in progress','pending developer response') NOT NULL DEFAULT 'untested',
  DescriptionChanged TINYINT(1) NULL,
  StepsToReproduceChanged TINYINT(1) NULL,
  Note text,
  PRIMARY KEY (HistoryID),
  INDEX Index_Content (ContentID)
);

CREATE TABLE IF NOT EXISTS Ticket_User (
  TicketID INTEGER UNSIGNED NOT NULL DEFAULT 0,
  UserID INTEGER UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (TicketID,UserID)
);

CREATE TABLE IF NOT EXISTS User (
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
  Active TINYINT(1) NOT NULL DEFAULT 1,
  Visible TINYINT(1) NOT NULL DEFAULT 1,
  Password VARCHAR(255) BINARY NOT NULL DEFAULT '',
  PRIMARY KEY (UserID),
  UNIQUE INDEX Unique_Email (Email)
);

CREATE TABLE IF NOT EXISTS User_Key (
  UserID INTEGER UNSIGNED NOT NULL,
  KeyID INTEGER UNSIGNED NOT NULL,
  Level INTEGER UNSIGNED NOT NULL DEFAULT 0,
  INDEX Unique_UserKey(UserID, KeyID)
);

INSERT INTO User (Email, FirstName, LastName, Visible) VALUES ('MasterAdmin', 'Master', 'Admin', 0);
INSERT INTO User_Key (UserID, KeyID, `Level`) VALUES ((SELECT u.UserID FROM `User` u WHERE u.Email = 'MasterAdmin'), (SELECT r.KeyID FROM ResourceKey r WHERE r.Name = 'Admin'), 1000);
