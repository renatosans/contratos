
CREATE TABLE OINS(
 insID              INT NULL,
 manufSN            VARCHAR(255) NULL,
 internalSN         VARCHAR(255) NULL,
 itemCode           INT NULL,
 itemName           VARCHAR(255) NULL,
 customer           INT NULL,
 custmrName         VARCHAR(255) NULL,
 contactPerson      INT NULL,
 addressType        INT NULL,
 street             VARCHAR(255) NULL,
 streetNo           VARCHAR(255) NULL,
 building           VARCHAR(255) NULL,
 zip                VARCHAR(255) NULL,
 block              VARCHAR(255) NULL,
 city               VARCHAR(255) NULL,
 state              VARCHAR(255) NULL,
 country            VARCHAR(255) NULL,
 instLocation       VARCHAR(255) NULL,
 status             CHAR(2) NULL,
 installationDate   DATE NULL,
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

