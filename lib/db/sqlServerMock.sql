

CREATE TABLE OINS(
 InsID              INT NULL,
 ManufSN            VARCHAR(255) NULL,
 InternalSN         VARCHAR(255) NULL,
 ItemCode           INT NULL,
 ItemName           VARCHAR(255) NULL,
 Customer           INT NULL,
 CustmrName         VARCHAR(255) NULL,
 ContactCod         INT NULL,
 AddrType           INT NULL,
 Street             VARCHAR(255) NULL,
 StreetNo           VARCHAR(255) NULL,
 Building           VARCHAR(255) NULL,
 Zip                VARCHAR(255) NULL,
 Block              VARCHAR(255) NULL,
 City               VARCHAR(255) NULL,
 State              VARCHAR(255) NULL,
 Country            VARCHAR(255) NULL,
 InstLction         VARCHAR(255) NULL,
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

INSERT INTO OINS(InsID, ItemCode, ItemName, status) VALUES (1, 123456, 'COPIADORA EP-1031', 'A')
UPDATE OINS SET U_InstallationDate = GETDATE(), U_InstallationDocNum = '', U_BwPageCounter = 123456, U_RemovalDate = GETDATE(), U_RemovalDocNum = '', U_BwPageCounter2 = 123456, U_Technician = 123, U_Model = 123, U_Capacity = '', U_SLA = '', U_Comments = '', U_SalesPerson = 123 WHERE InsId = 1
SELECT * FROM OINS
SELECT manufSN, status, COUNT(1) quantidade FROM OINS GROUP BY manufSN, status HAVING COUNT(1) > 1 AND status = 'A'
SELECT * FROM OINS WHERE (status = 'A' OR status = 'L') AND U_InstallationDate > GETDATE() ORDER BY manufSN


CREATE TABLE OCRD(
    CardCode    INT NULL,
    CardName    VARCHAR(255) NULL,
    CardFName   VARCHAR(255) NULL,
    FrozenFor   CHAR(2) NULL,
    CntctPrsn   INT NULL,
    Phone1      VARCHAR(255) NULL,
    IndustryC   INT NULL
)

INSERT INTO OCRD(CardCode, CardName) VALUES (1, 'CNSM LOGISTICA')
SELECT * FROM OCRD


DECLARE @ACCESSORIES TABLE (
    Code        INT NULL,
    U_InsId     INT NULL,
    U_ItemCode  INT NULL,
    U_ItemName  VARCHAR(255) NULL,
    U_Amount    INT NULL
)

INSERT INTO @ACCESSORIES(Code, U_ItemName, U_Amount) VALUES (1, 'Unidade de Fusão EP-6001', 2)
SELECT Code, U_InsId, U_ItemCode, U_ItemName, U_Amount FROM @ACCESSORIES WHERE Code = 1


CREATE TABLE OITM(
    ItemCode    INT NULL,
    ItemName    VARCHAR(255) NULL,
    ItmsGrpCod  INT NULL,
	AvgPrice    DECIMAL,
    UserText    VARCHAR(255) NULL,
    U_Expenses         VARCHAR(255) NULL,
    U_Durability       VARCHAR(255) NULL,
    U_SerializedData   VARCHAR(255) NULL,
    U_UseInstructions  VARCHAR(255) NULL,
)

INSERT INTO OITM(ItemCode, ItemName, ItmsGrpCod, AvgPrice) VALUES (1, 'Toner DCP8065DN', 100, 57)
INSERT INTO OITM(ItemCode, ItemName, ItmsGrpCod, AvgPrice) VALUES (2, 'Tampa Frontal DCP8065DN', 200, 320.99)
SELECT * FROM OITM


CREATE TABLE OITB(
    ItmsGrpCod   INT NULL,
    ItmsGrpNam   VARCHAR(255) NULL
)

INSERT INTO OITB(ItmsGrpCod, ItmsGrpNam) VALUES (100, 'CONSUMÍVEIS')
INSERT INTO OITB(ItmsGrpCod, ItmsGrpNam) VALUES (200, 'PEÇAS DE REPOSIÇÃO')
INSERT INTO OITB(ItmsGrpCod, ItmsGrpNam) VALUES (300, 'UNIDADES DE IMAGEM')
INSERT INTO OITB(ItmsGrpCod, ItmsGrpNam) VALUES (400, 'UNIDADES DE FUSÃO')
SELECT ItmsGrpCod, ItmsGrpNam FROM OITB ORDER BY ItmsGrpCod
