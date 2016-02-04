namespace BillingMailer
{
    partial class ProjectInstaller
    {
        /// <summary>
        /// Required designer variable.
        /// </summary>
        private System.ComponentModel.IContainer components = null;

        /// <summary> 
        /// Clean up any resources being used.
        /// </summary>
        /// <param name="disposing">true if managed resources should be disposed; otherwise, false.</param>
        protected override void Dispose(bool disposing)
        {
            if (disposing && (components != null))
            {
                components.Dispose();
            }
            base.Dispose(disposing);
        }

        #region Component Designer generated code

        /// <summary>
        /// Required method for Designer support - do not modify
        /// the contents of this method with the code editor.
        /// </summary>
        private void InitializeComponent()
        {
            this.mailingProcessInstaller = new System.ServiceProcess.ServiceProcessInstaller();
            this.mailingInstaller = new System.ServiceProcess.ServiceInstaller();
            // 
            // mailingProcessInstaller
            // 
            this.mailingProcessInstaller.Account = System.ServiceProcess.ServiceAccount.LocalSystem;
            this.mailingProcessInstaller.Password = null;
            this.mailingProcessInstaller.Username = null;
            // 
            // mailingInstaller
            // 
            this.mailingInstaller.Description = "Envia os dados de faturamento por email";
            this.mailingInstaller.ServiceName = "Billing Mailer";
            this.mailingInstaller.StartType = System.ServiceProcess.ServiceStartMode.Automatic;
            // 
            // ProjectInstaller
            // 
            this.Installers.AddRange(new System.Configuration.Install.Installer[] {
            this.mailingProcessInstaller,
            this.mailingInstaller});

        }

        #endregion

        private System.ServiceProcess.ServiceProcessInstaller mailingProcessInstaller;
        private System.ServiceProcess.ServiceInstaller mailingInstaller;
    }
}