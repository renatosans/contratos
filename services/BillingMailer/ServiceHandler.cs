using System;
using System.IO;
using System.Collections;
using System.ServiceProcess;
using System.Configuration.Install;


namespace BillingMailer
{
    /// <summary>
    /// Classe utilizada para o controle de execução de serviços do windows
    /// </summary>
    public static class ServiceHandler
    {
        /// <summary>
        /// Inicia um serviço do Windows, aguarda até o status mudar para "Running"
        /// </summary>
        public static void StartService(String serviceName, int timeout)
        {
            // Define o timeout da operação
            TimeSpan timeSpan = TimeSpan.FromMilliseconds(timeout);

            ServiceController service = new ServiceController(serviceName);
            service.Start();
            service.WaitForStatus(ServiceControllerStatus.Running, timeSpan);
        }

        /// <summary>
        /// Para um serviço do Windows, aguarda até o status mudar para "Stopped"
        /// </summary>
        public static void StopService(String serviceName, int timeout)
        {
            // Define o timeout da operação
            TimeSpan timeSpan = TimeSpan.FromMilliseconds(timeout);

            ServiceController service = new ServiceController(serviceName);
            service.Stop();
            service.WaitForStatus(ServiceControllerStatus.Stopped, timeSpan);
        }

        /// <summary>
        /// Acrescenta um serviço do windows no registro
        /// </summary>
        public static void InstallService(String fileName)
        {
            Directory.SetCurrentDirectory(Path.GetDirectoryName(fileName));
            String serviceName = Path.GetFileNameWithoutExtension(fileName);
            String[] arguments = new string[] { "/LogFile=" + serviceName + "_Install.log" };
            IDictionary state = new Hashtable();

            AssemblyInstaller installer = new AssemblyInstaller(fileName, arguments);
            installer.UseNewContext = true;
            installer.Install(state);
            installer.Commit(state);
        }

        /// <summary>
        /// Remove um serviço do windows do registro
        /// </summary>
        public static void UninstallService(String fileName)
        {
            Directory.SetCurrentDirectory(Path.GetDirectoryName(fileName));
            String serviceName = Path.GetFileNameWithoutExtension(fileName);
            String[] arguments = new string[] { "/LogFile=" + serviceName + "_Install.log" };

            AssemblyInstaller installer = new AssemblyInstaller(fileName, arguments);
            installer.UseNewContext = true;
            installer.Uninstall(null);
        }

        /// <summary>
        /// Determina se um serviço do windows está registrado/instalado
        /// </summary>
        public static Boolean ServiceExists(String serviceName)
        {
            ServiceController[] windowsServices = ServiceController.GetServices();
            foreach (ServiceController windowsService in windowsServices)
            {
                if (windowsService.ServiceName == serviceName) return true;
            }
            return false;
        }
    }

}
