

CREATE DATABASE [SBO_DATACOPY]
 CONTAINMENT = NONE
 ON  PRIMARY 
( NAME = N'SBO_DATACOPY', FILENAME = N'C:\Users\Public\SBO_DATACOPY.mdf' , SIZE = 8192KB , MAXSIZE = UNLIMITED, FILEGROWTH = 65536KB )
 LOG ON 
( NAME = N'SBO_DATACOPY_log', FILENAME = N'C:\Users\Public\SBO_DATACOPY_log.ldf' , SIZE = 8192KB , MAXSIZE = 2048GB , FILEGROWTH = 65536KB )
GO

USE [SBO_DATACOPY]

/*
    SELECT * FROM SYS.OBJECTS WHERE type_desc = 'USER_TABLE' AND
    name IN ('OINS', 'OCRD', 'OITM', 'OITB', 'OSCS', 'OSCT', 'OHEM', 'OHPS', 'OOND', 'OCPR', 'OMRC', 'OSLP')
*/


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

INSERT INTO OINS(InsID, ManufSN, InternalSN, ItemCode, ItemName, Customer, Status, U_Model) VALUES (1, 'A0V0011001804', 'EF4608', 123456, 'COPIADORA Bizhub PRO C6501', 3, 'A', 2)
INSERT INTO OINS(InsID, ManufSN, InternalSN, ItemCode, ItemName, Customer, Status, U_Model) VALUES (1, 'A5C0011032437', 'WX7005', 123456, 'Konica Minolta Bizhub C454e', 3, 'A', 2)
UPDATE OINS SET U_InstallationDate = GETDATE(), U_BwPageCounter = 123456, U_RemovalDate = GETDATE(), U_BwPageCounter2 = 123456 WHERE InsId = 1
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

INSERT INTO OCRD(CardCode, CardName, CardFName, IndustryC) VALUES (1, 'CNSM LOGISTICA', 'CNSM', 3)
INSERT INTO OCRD(CardCode, CardName, CardFName, IndustryC) VALUES (2, 'FSETE CONSULTORIA', 'FSETE', 2)
INSERT INTO OCRD(CardCode, CardName, CardFName, IndustryC) VALUES (3, 'GLOBE SERVIÇOS CONTABEIS', 'GLOBE', 8)


DECLARE @ACCESSORIES TABLE (
    Code        INT NULL,
    U_InsId     INT NULL,
    U_ItemCode  INT NULL,
    U_ItemName  VARCHAR(255) NULL,
    U_Amount    INT NULL
)

INSERT INTO @ACCESSORIES(Code, U_ItemName, U_Amount) VALUES (1, 'Unidade de Fusão EP-6001', 2)
SELECT Code, U_InsId, U_ItemCode, U_ItemName, U_Amount FROM @ACCESSORIES WHERE Code = 1


/* OITM - Items */
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
INSERT INTO OITM(ItemCode, ItemName, ItmsGrpCod, AvgPrice) VALUES (3, 'Bucha', 200, 35.99)
INSERT INTO OITM(ItemCode, ItemName, ItmsGrpCod, AvgPrice) VALUES (4, 'Suporte', 200, 150.99)
INSERT INTO OITM(ItemCode, ItemName, ItmsGrpCod, AvgPrice) VALUES (5, 'Unidade de Imagem', 200, 940.99)


/* OITB - Item Groups */
CREATE TABLE OITB(
    ItmsGrpCod   INT NULL,
    ItmsGrpNam   VARCHAR(255) NULL
)

INSERT INTO OITB(ItmsGrpCod, ItmsGrpNam) VALUES (100, 'CONSUMÍVEIS')
INSERT INTO OITB(ItmsGrpCod, ItmsGrpNam) VALUES (200, 'PEÇAS DE REPOSIÇÃO')
INSERT INTO OITB(ItmsGrpCod, ItmsGrpNam) VALUES (300, 'UNIDADES DE IMAGEM')
INSERT INTO OITB(ItmsGrpCod, ItmsGrpNam) VALUES (400, 'UNIDADES DE FUSÃO')


/* OSCS - Service Call Statuses */
CREATE TABLE OSCS(
    StatusID    INT NULL,
    Name        VARCHAR(255) NULL,
    Descriptio  VARCHAR(255) NULL
)

INSERT INTO OSCS(StatusID, Name) VALUES (1, 'ABERTO')
INSERT INTO OSCS(StatusID, Name) VALUES (2, 'FECHADO')
INSERT INTO OSCS(StatusID, Name) VALUES (3, 'CANCELADO')
INSERT INTO OSCS(StatusID, Name) VALUES (4, 'AGUARDANDO PEÇAS')


/* OSCT - Service Call Types */
CREATE TABLE OSCT(
    CallTypeID  INT NULL,
    Name        VARCHAR(255) NULL,
    Descriptio  VARCHAR(255) NULL
)

INSERT INTO OSCT(CallTypeID, Name) VALUES (1, 'Manutenção Preventiva')
INSERT INTO OSCT(CallTypeID, Name) VALUES (2, 'Manutenção Corretiva')
INSERT INTO OSCT(CallTypeID, Name) VALUES (3, 'Retorno')


/* OHEM - Employees */
CREATE TABLE OHEM(
    EmpID       INT NULL,
    FirstName   VARCHAR(255) NULL,
    MiddleName  VARCHAR(255) NULL,
    LastName    VARCHAR(255) NULL,
    Position    INT NULL,
    Email       VARCHAR(255) NULL
)

INSERT INTO OHEM(EmpID, FirstName, MiddleName, LastName, Position, Email) VALUES (1, 'ISABELA', 'CRISTINA', 'CASSIANO', 6, 'isabela@gmail.com');
INSERT INTO OHEM(EmpID, FirstName, MiddleName, LastName, Position, Email) VALUES (2, 'CAROLINA', 'MENEZES', 'DA COSTA', 2, 'carolina@terra.com.br');
INSERT INTO OHEM(EmpID, FirstName, MiddleName, LastName, Position, Email) VALUES (3, 'LUIZ', 'AUGUSTO TEIXEIRA', 'BRANCO', 3, 'luizaugusto@gmail.com');
INSERT INTO OHEM(EmpID, FirstName, MiddleName, LastName, Position, Email) VALUES (4, 'CAIO', 'BATISTA', 'CABRAL', 1, 'caiocabral@hotmail.com.br');
INSERT INTO OHEM(EmpID, FirstName, MiddleName, LastName, Position, Email) VALUES (5, 'VINICIUS', 'RODRIGUES FEITOZA', 'VILLACA', 3, 'vinicius@hotmail.com');
INSERT INTO OHEM(EmpID, FirstName, MiddleName, LastName, Position, Email) VALUES (6, 'GEILDA', 'CAMPOS ALVARIO', 'MARQUEZ', 1, 'geilda33@gmail.com');


/* OHPS - Employee Position */
CREATE TABLE OHPS(
    PosId   INT NULL,
    Name    VARCHAR(255) NULL
)

INSERT INTO OHPS(PosId, Name) VALUES (1, 'Vendedor')
INSERT INTO OHPS(PosId, Name) VALUES (2, 'Auxiliar')
INSERT INTO OHPS(PosId, Name) VALUES (3, 'Técnico')
INSERT INTO OHPS(PosId, Name) VALUES (4, 'Gerente')
INSERT INTO OHPS(PosId, Name) VALUES (5, 'Assistente')
INSERT INTO OHPS(PosId, Name) VALUES (6, 'Recursos Humanos')


/* OOND - Industries */
CREATE TABLE OOND(
    IndCode   INT NULL,
    IndName   VARCHAR(255) NULL,
	IndDesc   VARCHAR(255) NULL
)

INSERT INTO OOND(IndCode, IndName, IndDesc) VALUES (1, 'Telecom', 'Telecomunicações')
INSERT INTO OOND(IndCode, IndName, IndDesc) VALUES (2, 'Consultoria', 'Consultoria')
INSERT INTO OOND(IndCode, IndName, IndDesc) VALUES (3, 'Logistica', 'Transporte e Logística')
INSERT INTO OOND(IndCode, IndName, IndDesc) VALUES (4, 'Utilidades', 'Utilidades e Serviços Públicos')
INSERT INTO OOND(IndCode, IndName, IndDesc) VALUES (5, 'Siderurgica', 'Metalurgia e Siderurgia')
INSERT INTO OOND(IndCode, IndName, IndDesc) VALUES (6, 'Agropecuária', 'Agricultura e Pecuária')
INSERT INTO OOND(IndCode, IndName, IndDesc) VALUES (7, 'Varejo', 'Varejo')
INSERT INTO OOND(IndCode, IndName, IndDesc) VALUES (8, 'Contabil', 'Serviços Contábeis')


/* OCPR - Contact Persons */
CREATE TABLE OCPR(
    CntctCode   INT NULL,
    CardCode    INT NULL,
    Name        VARCHAR(255) NULL,
    Tel1        VARCHAR(255) NULL,
    Cellolar    VARCHAR(255) NULL,
    E_MailL     VARCHAR(255) NULL
)

INSERT INTO OCPR(CntctCode, CardCode, Name, Tel1, E_MailL) VALUES (1, 3, 'Gabrielle Lins da Costa', '997150782', 'gabrielle_lins@gmail.com.br')
INSERT INTO OCPR(CntctCode, CardCode, Name, Tel1, E_MailL) VALUES (2, 3, 'Joao Carlos Cardoso', '996481227', 'joao45@hotmail.com.br')
INSERT INTO OCPR(CntctCode, CardCode, Name, Tel1, E_MailL) VALUES (3, 2, 'Vicente Santos da Silva', '997296815', 'vicente_silva33@terra.com.br')
INSERT INTO OCPR(CntctCode, CardCode, Name, Tel1, E_MailL) VALUES (4, 2, 'Sarah Carvalho Mendonça', '997003449', 'sarah_mendonça@gmail.com.br')


/* OMRC - Manufacturers */
CREATE TABLE OMRC(
    FirmCode  INT NULL,
    FirmName  VARCHAR(255) NULL
)

INSERT INTO OMRC(FirmCode, FirmName) VALUES (1, 'Samsung')
INSERT INTO OMRC(FirmCode, FirmName) VALUES (2, 'Canon')
INSERT INTO OMRC(FirmCode, FirmName) VALUES (3, 'Lexmark')
INSERT INTO OMRC(FirmCode, FirmName) VALUES (4, 'Konica Minolta')
INSERT INTO OMRC(FirmCode, FirmName) VALUES (5, 'Brother')
INSERT INTO OMRC(FirmCode, FirmName) VALUES (6, 'Xerox')


/* OSLP - Sales Person */
CREATE TABLE OSLP(
    SlpCode           INT NULL,
    SlpName           VARCHAR(255) NULL,
	Commission        DECIMAL,
	U_SerializedData  TEXT
)

INSERT INTO OSLP(SlpCode, SlpName, Commission, U_SerializedData) VALUES (1, 'Candido Martins', 4.3, '')
INSERT INTO OSLP(SlpCode, SlpName, Commission, U_SerializedData) VALUES (2, 'Custodio Alcantara Neto', 3.9, '')
INSERT INTO OSLP(SlpCode, SlpName, Commission, U_SerializedData) VALUES (3, 'Maria de Menezes', 4.1, '')
INSERT INTO OSLP(SlpCode, SlpName, Commission, U_SerializedData) VALUES (4, 'Rodolfo Zimmerman', 4.2, '')
