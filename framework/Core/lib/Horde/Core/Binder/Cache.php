<?php
class Horde_Core_Binder_Cache implements Horde_Injector_Binder
{
    public function create(Horde_Injector $injector)
    {
        return $this->_getCacheInstance($GLOBALS['conf']['cache']['driver'], $injector);
    }

    protected function _getCacheInstance($driver, $injector)
    {
        $params = Horde::getDriverConfig('cache', $driver);

        if (is_array($driver)) {
            list($app, $driver_name) = $driver;
        } else {
            $app = 'Horde';
            $driver_name = basename($driver);
        }

        if (strtolower($app) == 'horde') {
            switch (strtolower($driver)) {
            case 'memcache':
                $params['memcache'] = $injector->getInstance('Horde_Memcache');
                break;

            case 'sql':
                if (!empty($params['use_memorycache'])) {
                    $params['use_memorycache'] = $this->_getCacheInstance($params['use_memorycache'], $injector);
                }
                break;
            }
        }

        $params['logger'] = $injector->getInstance('Horde_Log_Logger');

        return Horde_Cache::factory($driver, $params);
    }

    public function equals(Horde_Injector_Binder $binder)
    {
        return false;
    }
}
