using System;
using System.Diagnostics;


namespace BillingMailer
{
    public class TraceHandler: IListener
    {
        public void NotifyObject(Object obj)
        {
            if ( (obj is Exception) && EventLog.SourceExists("Billing Mailer") )
                EventLog.WriteEntry("Billing Mailer", "Trace -> " + Environment.NewLine + ((Exception)obj).Message);
        }
    }

}
