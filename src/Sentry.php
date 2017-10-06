<?php

namespace EETechMedia\Sentry;

use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;


use EETechMedia\Sentry\Models\Sentry as SentryModel;
use EETechMedia\Sentry\Traits\Possess;

/**
 * Sentr Main class
 * @author goper
 */
class Sentry
{
    use Possess;

    /**
     * Model
     * @var [type]
     */
    protected $sentry;

    /**
     * Main data
     * @var [type]
     */
    protected $ward;

    /**
     * Third party Jenssegers\Agent\Agent
     * @var [type]
     */
    protected $agent;

    /**
     * Geoip
     * @var [type]
     */
    protected $geoip;

    /**
     * Illuminate\Request
     * @var [type]
     */
    protected $request;

    /**
     * Sentry constructor
     */
    public function __construct(SentryModel $sentry, Agent $agent, Request $request)
    {
        $this->sentry = $sentry;
        $this->agent = $agent;
        $this->geoip = geoip();
        $this->request = $request;
    }

    /**
     * Add user details to `sentries` table
     * Add new data / row
     *
     * @param Request $request
     * @return string
     */
    public function plant($ward)
    {
        $this->ward = $ward;

        $sentry = $this->sentry;
        $sentry->base_id = $this->_getBaseId();
        $sentry->url = $this->_has('url');
        $sentry->user_id = $this->_getUserId();

        $sentry->ip = $this->request->ip();
        $sentry->details = $this->getObserverDetails();
        $sentry->save();
    }

    /**
     *  Get user/observer details / information
     *
     * @return json $details
     */
    public function getObserverDetails()
    {
        return json_encode(array_merge($this->_getObserverHeaders(), $this->_getObserverSpot()));
    }

    /**
     * Get observer spot like ip,
     * @return [type] [description]
     */
    public function _getObserverSpot($ip = null)
    {
        if (is_null($ip))
            $ip = $this->request->ip();

        return $this->geoip->getLocation($ip)->toArray();
    }

    /**
     * Get user details - like 'ip', 'country', 'location' etc..
     * @return array $details
     */
    public function _getObserverHeaders()
    {
        $agent = $this->agent;
        $details = [];
        $machine = '';

        if ($agent->isPhone()) {
            $machine = 'mobile';
        } elseif ($agent->isTablet()) {
            $machine = 'tablet';
        } else {
            $machine = 'computer';
        }

        $details = [
            'language' => $agent->languages()[0],
            'device' => $agent->device(),
            'os' => $agent->platform(),
            'browser' => $agent->browser(),
            'machine' => $machine,
            'robot' => $agent->isRobot(),
            'robot_name' => $agent->robot(),
        ];

        return $details;
    }


}
