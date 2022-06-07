
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
 InstLocation       VARCHAR(255) NULL,
 Status             CHAR(2) NULL,
 U_InstallationDate   DATE NULL,
 U_InstallationDocNum INT NULL,
 U_BwPageCounter      INT NULL,
 U_RemovalDate        DATE NULL,
 U_RemovalDocNum      INT NULL,
 U_BwPageCounter2     INT NULL,
 U_Technician         INT NULL,
 U_Model              INT NULL,
 U_Capacity           INT NULL,
 U_SLA                INT NULL,
 U_Comments           VARCHAR(255) NULL,
 U_SalesPerson        INT NULL
 )

SELECT * FROM OINS
SELECT manufSN, status, COUNT(1) quantidade FROM OINS GROUP BY manufSN, status HAVING COUNT(1) > 1 AND status = 'A'
SELECT * FROM OINS WHERE (status = 'A' OR status = 'L') AND U_InstallationDate > GETDATE() ORDER BY manufSN
INSERT INTO OINS(InsID, ItemCode, ItemName, status) VALUES (1, 123456, 'COPIADORA EP-1031', 'A')
UPDATE OINS SET U_InstallationDate = GETDATE(), U_InstallationDocNum = '', U_BwPageCounter = 123456, U_RemovalDate = GETDATE(), U_RemovalDocNum = '', U_BwPageCounter2 = 123456, U_Technician = 123, U_Model = 123, U_Capacity = '', U_SLA = '', U_Comments = '', U_SalesPerson = 123 WHERE InsId = 1


CREATE TABLE OCRD(
    CardCode    INT NULL,
    CardName    VARCHAR(255) NULL,
    CardFName   VARCHAR(255) NULL,
    FrozenFor   CHAR(2) NULL,
    CntctPrsn   INT NULL,
    Phone1      VARCHAR(255) NULL,
    IndustryC   INT NULL
)

SELECT * FROM OCRD
INSERT INTO OCRD(CardCode, CardName) VALUES (1, 'CNSM LOGISTICA')
