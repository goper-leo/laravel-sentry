<?php

namespace EETechMedia\Sentry;

use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

use EETechMedia\Sentry\Models\Sentry as SentryModel;
use EETechMedia\Sentry\Traits\Possess;

/**
 * Sentry Main class
 *
 * @TODO Create config that can be set to add redundant user like `daily`, 'monthly' etc
 * @author goper
 */
class Sentry
{
    use Possess;

    /**
     * Model `sentries`
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
     * Date range user want
     * @var array
     */
    protected $dateRange;

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

        // Check if this user already viewed this page/article/project
        if ($this->_checkObserverUnique()) {
            $sentry = $this->sentry;
            $sentry->base_id = $this->_getBaseId();
            $sentry->url = $this->_has('url');
            $sentry->user_id = $this->_getUserId();

            $sentry->ip = $this->_getObserIp();
            $sentry->details = $this->getObserverDetails();
            $sentry->save();
        }
    }

    /**
     * Get All views on this app or if arguments are present use it as `where` condiftion
     * If arguments `$primary_key` is null then fetch all views
     *
     * @return collection $sentries
     */
    public function getAll($primary_key = null)
    {
        if (is_null($primary_key)) {
            // No arguments so fetch all
            return $this->sentry->all();
        }

        $column = $this->_identifyPrimaryKey($primary_key);

        return $this->sentry->where($column, $primary_key)->get();
    }

    /**
     * Get
     * @param  [type] $parameters [description]
     * @return [type]             [description]
     */
    public function getWhere($parameters)
    {
        $whereClause = '';

        if (is_array($parameters)) {

            $whereParameters = $this->_compileWhere($parameters);

            // Check if we need the date range
            if ($this->_extractRange($parameters)) {
                $results = $this->sentry->where($whereParameters)->whereBetween('created_at', $this->dateRange)->get();
            } else {
                $results = $this->sentry->where($whereParameters)->get();
            }

            return $results;

        } else {
            // $parameters is a string - identify if this is used to be `id` or `url`
            return $this->getAll($parameters);
        }
    }

    /**
     *  Get user/observer details / information
     *
     * @return json $details
     */
    public function getObserverDetails()
    {
        return json_encode(array_merge($this->getObserverHeaders(), $this->getObserverSpot()));
    }

    /**
     * Get observer spot like ip,
     * @return [type] [description]
     */
    public function getObserverSpot($ip = null)
    {
        if (is_null($ip))
            $ip = $this->request->ip();

        return $this->geoip->getLocation($ip)->toArray();
    }

    /**
     * Get user details - like 'ip', 'country', 'location' etc..
     * @return array $details
     */
    public function getObserverHeaders()
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

    /**
     * Compile array where to a query -
     * check if date_from and date_to exist - if true query as range
     *
     * @param  [type] $parameters [description]
     * @return [type]        [description]
     */
    private function _compileWhere($parameters)
    {
        $whereClause = [];
        $sentry = $this->sentry;

        if (array_key_exists('id', $parameters)) {
            $whereClause = ['base_id' => $parameters['id']];
        }

        if (array_key_exists('base_id', $parameters)) {
            $whereClause = ['base_id' => $parameters['base_id']];
        }

        if (array_key_exists('url', $parameters)) {
            $whereClause = ['url' => $parameters['url']];
        }

        return $whereClause;
    }

    /**
     * Extract date range to query if exist
     * @param  [type] $parameters [description]
     */
    private function _extractRange($parameters)
    {
        // Get date from and date to
        if (array_key_exists('date_from', $parameters) && array_key_exists('date_to', $parameters)) {
            $dateFrom = $parameters['date_from'];
            $dateTo = $parameters['date_to'];

            if ($this->_isDateOnly($dateFrom)) {
                $dateFrom .= ' 00:00:00';
            }

            if ($this->_isDateOnly($dateTo)) {
                $dateTo .= ' 23:59:59';
            }

            $this->dateRange = [$dateFrom, $dateTo];
            return true;
        }

        return false;
    }

    /**
     * Check this observer / viewer if already exist on table
     *
     * @return [type] [description]
     */
    private function _checkObserverUnique()
    {
        $checker;
        $identifier = $this->_getViewerIdentity();

        if ($this->_getBaseId() == '') {
            // No base_id therefore expected primary is the `url`
            $checker = $this->sentry->where(array_merge(['url' => $this->ward['url']], $identifier))->count();

        } else {
            // Use base_id as primary key
            $baseId = $this->_getBaseId();
            $checker = $this->sentry->where(array_merge(['base_id' =>$baseId], $identifier))->count();
        }

        if ($checker > 0) {
            return false;
        }

        return true;
    }

    /**
     * Get observer ip address
     * @return string $ip_address
     */
    private function _getObserIp()
    {
        return $this->request->ip();
    }

}
