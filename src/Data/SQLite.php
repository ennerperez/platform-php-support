<?php

namespace Platform\Support\Data;

use Platform\Support;
use ReflectionException;
use ReflectionObject;
use stdClass;

abstract class SQLite implements IDbCommon
{
    public static function execute($connection, $query, array $params = NULL, $mode = NULL, $type = NULL)
    {
        if ($mode == null)
            $mode = ExecuteModes::__default;

        if ($type == null)
            $type = CommandType::__default;

        $command = $connection->prepare($query);

        if (is_array($params)) {
            $c = count($params) - 1;
            for ($i = 0; $i <= $c; $i++) {
                $item = $params[$i];
                if (is_array($item)) {
                    $command->bindValue($item[0], $item[1]);
                }
            }
        }

        switch ($mode) {
            case ExecuteModes::NonQuery:
                $result = $command->execute();
                if ($result != FALSE) {
                    $rows = 0;
                    while ($data = $result->fetchArray()) {
                        $rows++;
                    }
                    $return = $rows;
                }
                break;
            case ExecuteModes::Scalar:
                $result = $command->execute();
                if ($result != FALSE) {
                    while ($data = $result->fetchArray()) {
                        $return = $data[0];
                        break;
                    }
                }
                break;
            case ExecuteModes::Reader:
                $result = $command->execute();
                if ($result != FALSE) {
                    $return = array();
                    while ($data = $result->fetchArray()) {
                        $return[] = $data;
                    }
                    break;
                }
                break;
            default:
                $return = $result;
                break;
        }
        $connection->close();
        return $return;

    }

    public static function fetchObject($sqlite3result, $objectType = NULL)
    {
        $array = $sqlite3result->fetchArray();
        if ($objectType == null) {
            $object = new stdClass();
        } else {
            // does not call this class' constructor
            $object = unserialize(sprintf('O:%d:"%s":0:{}', strlen($objectType), $objectType));
        }
        $reflector = new ReflectionObject($object);
        for ($i = 0; $i < $sqlite3result->numColumns(); $i++) {
            $name = $sqlite3result->columnName($i);
            $value = $array[$name];
            try {
                $attribute = $reflector->getProperty($name);
                $attribute->setAccessible(TRUE);
                $attribute->setValue($object, $value);
            } catch (ReflectionException $e) {
                $object->$name = $value;
            }
        }
        return $object;
    }
}