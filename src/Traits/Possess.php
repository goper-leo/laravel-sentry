<?php

namespace EETechMedia\Sentry\Traits;

trait Possess
{

    /**
     * Get base_id on request if this request has no `base_id`
     * Find other clue to get or return empty ''
     *
     * @param  [type] $request [description]
     * @return string $base_id
     */
    public function _getBaseId()
    {
        if ($this->_keyOccur('base_id'))
            return $this->ward['base_id'];
        elseif ($this->_keyOccur('baseId'))
            return $this->ward['baseId'];
        else
            return '';
    }

    /**
     * Get `user_id` check $seed has key of user_id if false check user is logged_in or get from that
     * @param  [type] $request [description]
     * @return [type]          [description]
     */
    public function _getUserId()
    {
        if ($this->_keyOccur('user_id')) {
            return $this->ward['user_id'];
        } elseif ($this->_keyOccur('userId')) {
            return $this->ward['userId'];
        } else {
            // Get user id from `Auth`
            if (\Auth::check()) {
                return \Auth::id();
            } else {
                return '';
            }
        }

        return '';
    }

    /**
     * Check if given key occur or exist on array given `$seed`
     * @return boolean
     */
    public function _keyOccur($key)
    {
        if (array_key_exists($key, $this->ward))
            return true;

        return false;
    }

    /**
     * Check key if it contains if false return empty ''
     * @param  [type]  $key [description]
     * @return string $value
     */
    private function _has($key)
    {
        if ($this->_keyOccur($key)) {
            return $this->ward[$key];
        } else {
            return '';
        }
    }

    /**
     * Get primary key that are defined either base_id or url
     * @param  [type] $parameters [description]
     * @return string $column
     */
    public function _identifyPrimaryKey($primary_key)
    {
        // Identify $primary_key is a string or can be an int
        if (is_numeric($primary_key)) {
            // This is a base_id it is numeric
            $column = 'base_id';
        } else {
            // Use url as primary key
            $column = 'url';
        }

        return $column;
    }

    /**
     * Identify `$dateRange` variable is in date only or date with time
     * @return string $date
     */
    private function _isDateOnly($date)
    {
        if (!str_contains($date, ':')) {
            return true;
        }

        return false;
    }

    /**
     * Get viewer identity on the `$ward` if `user_id` exists or not
     * if not exist use ip as identifier
     *
     * @return array $identifier
     */
    private function _getViewerIdentity()
    {
        $identifier = [];
        $_user = $this->_getUserId();
        
        if ($_user != '') {
            $identifier = ['user_id' => $_user];
        } else {
            $identifier = ['ip' => $this->_getObserIp()];
        }

        return $identifier;
    }
}
