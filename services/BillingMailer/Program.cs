using System;
using System.Reflection;
using System.Diagnostics;
using System.ServiceProcess;


namespace BillingMailer
{
    static class Program
    {
        /// <summary>
        /// The main entry point for the application.
        /// </summary>
        static void Main(String[] args)
        {
            String serviceName = "Billing Mailer";
            String serviceLocation = Assembly.GetExecutingAssembly().Location;

            foreach (String argument in args)
            {
                if (argument.ToUpper().Contains("/RUNONCE"))
                {
                    // Registra o programa no Event Viewer para log de eventos
                    if (!EventLog.SourceExists("Billing Mailer"))
                        EventLog.CreateEventSource("Billing Mailer", "Application");

                    // Dispara método estático da classe principal, executa apenas uma vez
                    MailerService.HandleMailing(null, null);
                    return;
                }

                if (argument.ToUpper().Contains("/INSTALL"))
                {
                    // Se não estiver instalado na máquina faz sua instalação
                    if (!ServiceHandler.ServiceExists(serviceName))
                        ServiceHandler.InstallService(serviceLocation);

                    // Se não estiver em execução, inicia o serviço
                    ServiceController serviceController = new ServiceController(serviceName);
                    if (serviceController.Status != ServiceControllerStatus.Running)
                        ServiceHandler.StartService(serviceName, 33000);

                    // Registra o programa no Event Viewer para log de eventos
                    if (!EventLog.SourceExists("Billing Mailer"))
                        EventLog.CreateEventSource("Billing Mailer", "Application");

                    return;
                }

                if (argument.ToUpper().Contains("/UNINSTALL"))
                {
                    // Se não estiver instalado na máquina não é preciso fazer nada
                    if (!ServiceHandler.ServiceExists(serviceName)) return;

                    // Se estiver em execução, para o serviço
                    ServiceController serviceController = new ServiceController(serviceName);
                    if (serviceController.Status == ServiceControllerStatus.Running)
                        ServiceHandler.StopService(serviceName, 33000);

                    // Faz a remoção do serviço
                    ServiceHandler.UninstallService(serviceLocation);

                    return;
                }
            }

            ServiceBase[] ServicesToRun;
            ServicesToRun = new ServiceBase[] 
			{ 
				new MailerService() 
			};
            ServiceBase.Run(ServicesToRun);
        }
    }

}
