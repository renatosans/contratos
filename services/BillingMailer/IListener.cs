using System;


namespace BillingMailer
{
    /// <summary>
    /// Interface para troca de mensagens (entre dois objetos)
    /// </summary>
    public interface IListener
    {
        void NotifyObject(Object obj);
    }

}
