using System;
using System.Data.Common;
using System.Data.SqlClient;
using MySql.Data.MySqlClient;


namespace DataAccessObjects
{
    public abstract class DataAccessBase
    {
        protected MySqlConnection mySqlConnection;

        protected SqlConnection sqlServerConnection;


        protected String GetStringValue(DbDataReader dataReader, String fieldName)
        {
            if (dataReader[fieldName] is DBNull) return null;
            return (String)dataReader[fieldName];
        }

        protected int? GetIntegerValue(DbDataReader dataReader, String fieldName)
        {
            if (dataReader[fieldName] is DBNull) return null;
            return (int)dataReader[fieldName];
        }

        protected DateTime? GetDateTimeValue(DbDataReader dataReader, String fieldName)
        {
            if (dataReader[fieldName] is DBNull) return null;
            return (DateTime)dataReader[fieldName];
        }
    }

}
