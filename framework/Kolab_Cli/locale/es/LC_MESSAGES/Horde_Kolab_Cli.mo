��          �      |      �  �  �  �  �  z  k  �  �
  �   �    �     �     �  ~  �  $   K  d   p  r   �  S   H  9   �  �  �     �  /   �  4   �       h   +  8   �  �  �  �  {    z  �  �  �  j!    &#  N  ($     w&  "   �&  �  �&  5   t(  �   �(  x   ,)  e   �)  3   *  �  ?*  #   ,  4   :,  6   o,  (   �,  �   �,  :   [-                                                     
                           	                   account - Handles operations on an account level (like listing *all* available groupware objects)

  - all [TYPE]       : List all groupware objects of the account (optionally
                       limit to TYPE)
  - defects [TYPE]   : List all defects of the account (optionally limit to
                       TYPE)
  - issuelist [TYPE] : A brief list of issues of the account (optionally
                       limit to TYPE)


   data - Handle Kolab data (the default action is "info"). PATH refers to the path of the folder that holds the data and the optional TYPE argument indicates which data type should be read. This is usually already defined by the folder setting.

  - info      PATH               : Display general information.
  - stamp     PATH               : Display the folder status information.
  - ids       PATH TYPE          : Display all object ids in the folder PATH of
                                   type TYPE.
  - complete  PATH BACKENDID     : Return the complete message from folder PATH
                                   for the given BACKENDID.
  - create    PATH TYPE yaml PATH: Create an object as defined in the specified
                                   YAML data
  - backendid PATH TYPE OBJECTID : Return the backend ID for the object with ID
                                   OBJECTID.
  - delete    PATH TYPE ID,ID,.. : Delete the given object id's.

   folder - Handle a single folder (the default action is "show")

  - show      PATH         : Display information about the folder at PATH.
  - create    PATH [TYPE]  : Create the folder PATH (with the optional type TYPE).
  - delete    PATH         : Delete the folder PATH.
  - rename    OLD NEW      : Rename the folder from OLD to NEW.
  - getacl    PATH         : Get all ACL on the specified folder.
  - getmyacl  PATH         : Get your ACL on the specified folder.
  - setacl    PATH USER ACL: Set the ACL for the specified user on the folder.
  - deleteacl PATH USER ACL: Delete the ACL for the specified user on the folder.
  - getdesc   PATH         : Return the share description of the specified folder.
  - setdesc   PATH DESC    : Set the share description of the specified folder to DESC.
  - getshare  PATH         : Return the share parameters of the specified folder.


   format - Handle the Kolab format (the default action is "read")

  - read TYPE [FILE|FOLDER UID PART]: Read a Kolab format file of the specified
                                      type. Specify either a direct file name
                                      or a combination of an IMAP folder, a UID
                                      within that folder and the specific part
                                      that should be parsed.


   ledger - Handle ledger data in a Kolab backend (the default action is "display").

  - display            : Display all ledgers.
  - import FOLDER FILE : Import ledger XML data stored in FILE into Kolab folder FOLDER.

   list - Handle folder lists (the default action is "folders")

  - folders          : List the folders in the backend
  - types            : Display all folders that have a folder type.
  - type TYPE        : Display the folders of type TYPE.
  - owners           : List all folders and their owners.
  - defaults         : List the default folders for all users.
  - aclsupport       : Display if the server supports ACL.
  - namespace        : Display the server namespace information.
  - sync             : Synchronize the cache.


 %s is no local file! Action %s not supported! Activates the IMAP debug log. This will log the full IMAP communication - CAUTION: the "php" driver is the only driver variant that does not support this feature. For most drivers you should use "STDOUT" which will direct the debug log to your screen. For the horde, the horde-php, and the roundcube drivers you may also set this to a filename and the output will be directed there. Deactivate caching of the IMAP data. Path to the configuration file. Comman line parameters overwrite values from the configuration file. Produce time measurements to indicate how long the processing takes. You *must* activate logging for this as well. Report memory consumption statistics. You *must* activate logging for this as well. Sets the connection type. Use either "tls" or "ssl" here. The Kolab backend driver that should be used.
Choices are:

 - horde     [IMAP]: The Horde_Imap_Client driver as pure PHP implementation.
 - horde-php [IMAP]: The Horde_Imap_Client driver based on c-client in PHP
 - php       [IMAP]: The PHP imap_* functions which are based on c-client
 - pear      [IMAP]: The PEAR-Net_IMAP driver
 - roundcube [IMAP]: The roundcube IMAP driver
 - mock      [Mem.]: A dummy driver that uses memory. The host that holds the data. The password of the user accessing the backend. The port that should be used to connect to the host. The user accessing the backend. Write a log file in the provided LOG location. Use "STDOUT" here to direct the log output to the screen. [options] MODULE ACTION

Possible MODULEs and ACTIONs:

 Project-Id-Version: Horde_Kolab_Cli 
Report-Msgid-Bugs-To: dev@lists.horde.org
POT-Creation-Date: 2012-01-17 12:38+0100
PO-Revision-Date: 2012-01-17 12:38+0100
Last-Translator: Manuel P. Ayala <mayala@unex.es>, Juan C. Blanco <jcblanco@fi.upm.es>
Language-Team: i18n@lists.horde.org
Language: es
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
Plural-Forms: nplurals=2; plural=(n != 1);
   account - Gestiona las operaciones en el ámbito de las cuentas (tales como listar *todos* los objetos groupware disponibles)

  - all [TIPO]       : Listia todos los objetos groupware de la cuenta (optionalmente
                       limitándolos al TIPO)
  - defects [TIPO]   : Lista todos los defectos de la cuenta (optionalmente limitándolos al
                       TIPO)
  - issuelist [TIPO] : Lista reducida de problemas de la cuenta (optionalmente
                       limitándolos al TIPO)


   data - Gestiona los datos Kolab (la acción por omisión es "info"). RUTA se refiere a la de la carpeta que contiene los datos y el argumento opcional TIPO indica el tipo de datos que hay que leer. Ésto normalmente ya está definido mediante los ajustes de carpeta.

  - info      RUTA               : Muestra información general.
  - stamp     RUTA               : Muestra la información de estado de la carpeta.
  - ids       RUTA TIPO          : Muestra los ids de todos los objetos de la RUTA 
                                   de la carpeta de tipo TIPO.
  - complete  RUTA IDSOPORTE     : Devuelve el mensaje completo de la RUTA de la carpeta
                                   del IDSOPORTE indicado.
  - create    RUTA TIPO yaml RUTA: Crea un objeto tal y como se haya definido en los
                                   datos YAML
  - backendid RUTA TIPO IDOBJETO : Devuelve el ID del soporte del objeto con ID
                                   IDOBJETO.
  - delete    RUTA TIPO ID,ID,.. : Elimina los ids de objeto indicados.

   folder - Gestiona una única carpeta (la acción por omisión es "show")

  - show      RUTA         : Muestra información sobre la carpeta de la RUTA.
  - create    RUTA [TIPO]  : Crea la RUTA de la carpeta (con el TIPO opcional).
  - delete    RUTA         : Elimina la RUTA de la carpeta.
  - rename    ANTIGUO NUEVO: Renombra la carpeta de ANTIGUO a NUEVO.
  - getacl    RUTA         : Obtiene todas las ACL de la carpeta indicada.
  - getmyacl  RUTA         : Obtiene su ACL en la carpeta indicada.
  - setacl    RUTA USUA ACL: Define la ACL en la carpeta para el usuario indicado.
  - deleteacl RUTA USUA ACL: Elimina la ACL de la carpeta para el usuario indicado.
  - getdesc   RUTA         : Devuelve la descripción del recurso compartido de la carpeta indicada.
  - setdesc   RUTA DESC    : Define la descripción del recurso compartido de la carpeta indicada como DESC.
  - getshare  RUTA         : Devuelve los parámetros del recurso compartido de la carpeta indicada.


   format - Gestiona el formato Kolab (la acción por omisión es "read")

  - read TYPE [FILE|FOLDER UID PART]: Lee un archivo de formato Kolab del tipo indicado.
                                      Indique bien un nombre de archivo directamente o una 
                                      combinación de carpeta IMAP, un UID dentro de dicha 
                                      carpeta y la parte específica que habría que procesar


   ledger - Gestiona los datos de registro de un soporte Kolab (la acción por omisión es "display").

  - display            : Mostrar todos los registros.
  - import CARPETA ARCH: Importar a la CARPETA kolab datos XML de resgitro almacenados en el ARCH.

   list - Handle folder lists (the default action is "folders")

  - folders          : Lista las carpetas del soporte
  - types            : Muestra todas las carpetas con tipo de carpeta.
  - type TIPO        : Muestra todas las carpetas del tipo TIPO.
  - owners           : Muestra todas las carpetas y sus propietarios.
  - defaults         : Lista las carpetas por omisión de todos los usuarios.
  - aclsupport       : Muestra si el servidor admite ACL.
  - namespace        : Muestra la información de espacio de nombres del servidor.
  - sync             : Sincroniza la caché.


 ¡%s no es un archivo local! ¡La acción %s no está admitida! Activa el registro de depuración IMAP. Se registrarán todas las comunicaciones IMAP - PRECAUCIÓN: el controlador "php" es la única variante que no admite esta característica. Para la mayoría de los controladores hay que usar "STDOUT" con lo que se redirigirá el registro de depuración a la pantalla. Con los controladores de horde, horde-php y roudcube también se puede indicar un nombre de archivo y la salida se redirigirá al mismo. Desactivar almacenamiento de datos IMAP en la caché. Ruta al archivo de configuración. Los parámetros de línea de órdenes sobreescriben los valores del archivo de configuración. Genera medidas de tiempo para mostrar cuánto dura el proceso. *Hay* que activar también el registro para que funcione. Genera estadísticas de consumo de memoria. *Hay* que activar el registro también para que funcione. Define el tipo de conexión. Utilice "tls" o "ssl". El controlador del soporte Kolab que se usará.
Las opciones son:

 - horde     [IMAP]: El controlador Horde_Imap_Client como implementación PHP pura.
 - horde-php [IMAP]: El controlador Horde_Imap_Client basadi en c-client en PHP
 - php       [IMAP]: Las funciones imap_* de PHP basadas en c-client
 - pear      [IMAP]: El controlador PEAR-Net_IMAP
 - roundcube [IMAP]: El controlador IMAP de roundcube
 - mock      [Mem.]: Un controlador falso que utiliza la memoria. El servidor que almacena los datos. Contraseña del usuario que accede al soporte Kolab. Puerto que hay que utilizar para conectar al servidor. El usuario que acceder al soporte Kolab. Escribe un archivo de registro en la ubicación  de REGISTRO indicada. Utilice "STDOUT" para redirigir la salida de registro a la pantalla. [options] MÓDULO ACCIÓN

MÓDULOs y ACCIONes posibles:

 