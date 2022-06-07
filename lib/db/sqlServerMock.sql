
CREATE TABLE OINS(
 InsID              INT NULL,
 ManufSN            VARCHAR(255) NULL,
 InternalSN         VARCHAR(255) NULL,
 ItemCode           INT NULL,
 ItemName           VARCHAR(255) NULL,
 Customer           INT NULL,
 CustmrName         VARCHAR(255) NULL,
 ContactPerson      INT NULL,
 AddressType        INT NULL,
 Street             VARCHAR(255) NULL,
 StreetNo           VARCHAR(255) NULL,
 Building           VARCHAR(255) NULL,
 Zip                VARCHAR(255) NULL,
 Block              VARCHAR(255) NULL,
 City               VARCHAR(255) NULL,
 State              VARCHAR(255) NULL,
 Country            VARCHAR(255) NULL,
 instLocation       VARCHAR(255) NULL,
 status             CHAR(2) NULL,
 U_InstallationDate DATE NULL,
 installationDocNum INT NULL,
 counterInitialVal  INT NULL,
 removalDate        DATE NULL,
 removalDocNum      INT NULL,
 counterFinalVal    INT NULL,
 technician         INT NULL,
 model              INT NULL,
 capacity           INT NULL,
 sla                INT NULL,
 comments           VARCHAR(255) NULL,
 salesPerson        INT NULL
 )

SELECT * FROM OINS
SELECT manufSN, status, COUNT(1) quantidade FROM OINS GROUP BY manufSN, status HAVING COUNT(1) > 1 AND status = 'A'
SELECT * FROM OINS WHERE (status = 'A' OR status = 'L') AND U_InstallationDate > GETDATE() ORDER BY manufSN
UPDATE OINS SET U_InstallationDate = GETDATE(), U_InstallationDocNum = '', U_BwPageCounter = 123456, U_RemovalDate = GETDATE(), U_RemovalDocNum = '', U_BwPageCounter2 = 123456, U_Technician = 123, U_Model = 123, U_Capacity = '', U_SLA = '', U_Comments = '', U_SalesPerson = 123 WHERE InsId = 1
