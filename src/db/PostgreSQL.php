<?php

/**
 * @copyright Copyright (c) 2020 Alonso Mora
 * @license   https://github.com/amoracr/yii2-backup/blob/master/LICENSE.md
 * @link      https://github.com/amoracr/yii2-backup#readme
 * @author    Alonso Mora <adelfunscr@gmail.com>
 */

namespace amoracr\backup\db;

use amoracr\backup\db\Database;
use Yii;

/**
 * Component for dumping and restoring database data for MySql databases
 *
 * @author Alonso Mora <adelfunscr@gmail.com>
 * @since 1.1.0
 */
class PostgreSQL extends Database
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (empty($this->dumpCommand)) {
            $this->dumpCommand = 'pg_dump --no-owner --dbname=postgresql://{username}:{password}@{host}:{port}/{db}';
        }
        if (empty($this->loadCommand)) {
            $this->loadCommand = '';
        }
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException if configuration is not valid
     */
    public function dumpDatabase($dbHandle, $path)
    {
        $this->validateDumpCommand();
        $dumpCommand = $this->prepareCommand($dbHandle, $this->dumpCommand);
        $file = Yii::getAlias($path) . DIRECTORY_SEPARATOR . $dbHandle . '.sql';
        $command = sprintf("%s > %s  2> /dev/null", $dumpCommand, $file);
        system($command);

        if (!file_exists($file)) {
            return false;
        }

        return $file;
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException if configuration is not valid
     */
    public function importDatabase($dbHandle, $file)
    {
        
    }

    /**
     * @inheritdoc
     */
    protected function prepareCommand($dbHandle, $templateCommand)
    {
        $command = $templateCommand;
        $dsn = str_replace('pgsql:', '', Yii::$app->$dbHandle->dsn);
        $dbParams = explode(';', $dsn);
        $database = '';
        $port = '';
        foreach ($dbParams as $param) {
            if (stripos('port', $param) !== false) {
                list($tmp, $port) = explode('=', $param);
            }
            if (stripos('dbname', $param) !== false) {
                list($tmp, $database) = explode('=', $param);
            }
        }

        $params = [
            'username' => Yii::$app->$dbHandle->username,
            'host' => 'localhost',
            'password' => Yii::$app->$dbHandle->password,
            'db' => $database,
            'port' => $port,
        ];

        if ((string) $params['password'] === '') {
            $command = str_replace(':{password}', '', $command);
            unset($params['password']);
        }

        return $this->replaceParams($command, $params);
    }

}