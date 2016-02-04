using System;
using System.Data.SqlClient;
using System.Collections.Generic;
using DataTransferObjects;


namespace DataAccessObjects
{
    public class EquipmentDAO: DataAccessBase
    {
        public EquipmentDAO(SqlConnection sqlServerConnection)
        {
            this.sqlServerConnection = sqlServerConnection;
        }

        public List<EquipmentDTO> GetCustomerEquipments(String customerCardCode)
        {
            List<EquipmentDTO> equipmentList = new List<EquipmentDTO>();

            String query = "SELECT InsID, ManufSN, InternalSN, ItemCode, ItemName, AddrType, Street, StreetNo, Building, Zip, Block, City, State, County, Country FROM OINS WHERE Customer = '" + customerCardCode + "' AND status = 'A' ORDER BY ManufSN";
            SqlCommand command = new SqlCommand(query, sqlServerConnection);
            SqlDataReader dataReader = command.ExecuteReader();
            while (dataReader.Read())
            {
                EquipmentDTO equipment = new EquipmentDTO();
                equipment.id = (int) dataReader["InsID"];
                equipment.ManufSN = GetStringValue(dataReader, "ManufSN");
                equipment.InternalSN = GetStringValue(dataReader, "InternalSN");
                equipment.ItemCode = GetStringValue(dataReader, "ItemCode");
                equipment.ItemName = GetStringValue(dataReader, "ItemName");
                equipment.AddrType = GetStringValue(dataReader, "AddrType");
                equipment.Street = GetStringValue(dataReader, "Street");
                equipment.StreetNo = GetStringValue(dataReader, "StreetNo");
                equipment.Block = GetStringValue(dataReader, "Block");
                equipment.Building = GetStringValue(dataReader, "Building");
                equipment.Zip = GetStringValue(dataReader, "Zip");
                equipment.City = GetStringValue(dataReader, "City");
                equipment.State = GetStringValue(dataReader, "State");
                equipment.County = GetStringValue(dataReader, "County");
                equipment.Country = GetStringValue(dataReader, "Country");

                equipmentList.Add(equipment);
            }
            dataReader.Close();

            return equipmentList;
        }

        // Recebe como parâmetro os ids separados por vírgula
        public List<EquipmentDTO> GetEquipments(String equipmentIds)
        {
            List<EquipmentDTO> equipmentList = new List<EquipmentDTO>();

            if (String.IsNullOrEmpty(equipmentIds)) equipmentIds = "0";
            String query = "SELECT ManufSN, InternalSN, ItemCode, ItemName, Customer FROM OINS WHERE InsID IN (" + equipmentIds + ") AND status = 'A' ORDER BY ManufSN";
            SqlCommand command = new SqlCommand(query, sqlServerConnection);
            SqlDataReader dataReader = command.ExecuteReader();
            while (dataReader.Read())
            {
                EquipmentDTO equipment = new EquipmentDTO();
                equipment.ManufSN = GetStringValue(dataReader, "ManufSN");
                equipment.InternalSN = GetStringValue(dataReader, "InternalSN");
                equipment.ItemCode = GetStringValue(dataReader, "ItemCode");
                equipment.ItemName = GetStringValue(dataReader, "ItemName");
                equipment.Customer = GetStringValue(dataReader, "Customer");

                equipmentList.Add(equipment);
            }
            dataReader.Close();

            return equipmentList;
        }

        public void SetSLA(int equipmentCode, int sla)
        {
            String commandText = "UPDATE OINS SET U_SLA=" + sla + " WHERE InsID = " + equipmentCode;
            SqlCommand command = new SqlCommand(commandText, this.sqlServerConnection);
            command.ExecuteNonQuery();
        }
    }

}
