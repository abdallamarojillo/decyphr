<?php

namespace app\models;

/**
 * Class LogType
 * Central constants for log types
 */
class LogType
{
    const REQUEST  = 'REQUEST';
    const API      = 'API';
    const AUTH     = 'AUTH';
    const SECURITY = 'SECURITY';
    const ERROR    = 'ERROR';
    const AUDIT    = 'AUDIT';
    const INFO     = 'INFO';
    const WARNING  = 'WARNING';
    const RECORD_CHANGE     = 'RECORD_CHANGE';
}