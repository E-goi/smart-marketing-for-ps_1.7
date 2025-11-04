<?php
/**
 * Debug Logger Helper - Wraps PrestaShopLogger with Debug Mode check
 *
 * @author    E-goi
 * @copyright 2018 E-goi
 * @license   LICENSE.txt
 */

class DebugLogger
{
    /**
     * Log a message only if Debug Mode is enabled
     *
     * @param string $message The message to log
     * @return bool
     */
    public static function log($message)
    {
        // Only log if debug mode is enabled
        if (!Configuration::get('EGOI_DEBUG_MODE')) {
            return false;
        }

        DebugLogger::log($message);
        return true;
    }

    /**
     * Clear all EGOI logs from PrestaShop logs (both debug and test logs)
     *
     * @return bool
     */
    public static function clearLogsEgoi()
    {
        $db = Db::getInstance();

        try {
            // Delete all logs that contain [EGOI-PS8]
            $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'log` WHERE `message` LIKE "%[EGOI-PS17%" OR `message` LIKE "%[EGOI-PS1.7]%" OR `message` LIKE "%[EGOI::%";';
            $result = $db->execute($sql);

            return $result !== false;
        } catch (Exception $e) {
            // Log error to PrestaShop error log
            error_log('DebugLogger::clearLogsEgoi error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear only debug logs (not test logs)
     *
     * @return bool
     */
    public static function clearLogs()
    {
        $db = Db::getInstance();

        try {
            // Delete only debug logs [EGOI-PS8]:: (not test logs)
            $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'log` WHERE `message` LIKE "%[EGOI-PS8]::%"';
            $result = $db->execute($sql);

            return $result !== false;
        } catch (Exception $e) {
            // Log error to PrestaShop error log
            error_log('DebugLogger::clearLogs error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear EGOI logs and disable Debug Mode
     *
     * @return bool
     */
    public static function disableDebugMode()
    {
        // Clear logs first
        self::clearLogs();

        // Disable Debug Mode
        Configuration::updateValue('EGOI_DEBUG_MODE', 0);

        return true;
    }
}