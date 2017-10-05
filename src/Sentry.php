<?php

namespace EETechMedia\Sentry;

use Illuminate\Http\Request;
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
     * User other details
     * @var [type]
     */
    protected $details;

    /**
     * Sentry constructor
     */
    public function __construct(SentryModel $sentry)
    {
        $this->sentry = $sentry;
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

        $sentry->ip = '$request->ip';
        $sentry->details = json_encode($this->ward); // Chunk details
        $sentry->save();

        echo 'save';
    }

    private function _getObserverDetails()
    {
        # code...
    }
}
