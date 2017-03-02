<?php

class Config
{
    protected $_allowedYaku = [];
    protected $_startPoints = 0;
    protected $_withKazoe = false;
    protected $_withKiriageMangan = false;
    protected $_withAbortives = false;
    protected $_withNagashiMangan = false;
    protected $_withAtamahane = false;
    protected $_rulesetTitle = '';
    protected $_tenboDivider = 1;
    protected $_ratingDivider = 1;
    protected $_tonpuusen = false;
    protected $_startRating = 0;
    protected $_riichiGoesToWinner = false;
    protected $_extraChomboPayments = false;
    protected $_chomboPenalty = 0;
    protected $_withKuitan = false;
    protected $_withButtobi = false;
    protected $_withMultiYakumans = false;
    protected $_autoRegisterUsers = false;
    protected $_gameExpirationTime = 0;
    protected $_minPenalty = 0;
    protected $_maxPenalty = 0;
    protected $_penaltyStep = 0;
    protected $_eventTitle = '';
    protected $_eventStatHost = '';
    protected $_redZone = 0;
    protected $_autoSeating = false;
    protected $_isOnline = false;
    protected $_gameDuration = 0;
    protected $_withLeadingDealerGameover = false;
    
    public static function fromRaw($arr)
    {
        $cfg = new self();
        foreach ($arr as $k => $v) {
            $k = '_' . $k;
            if (!isset($cfg->{$k})) {
                continue;
            }

            $cfg->{$k} = $v;
        }

        return $cfg;
    }
    
    /**
     * @return array
     */
    public function allowedYaku()
    {
        return $this->_allowedYaku;
    }
    /**
     * @return int
     */
    public function startPoints()
    {
        return $this->_startPoints;
    }
    /**
     * @return boolean
     */
    public function withKazoe()
    {
        return $this->_withKazoe;
    }
    /**
     * @return boolean
     */
    public function withKiriageMangan()
    {
        return $this->_withKiriageMangan;
    }
    /**
     * @return boolean
     */
    public function withAbortives()
    {
        return $this->_withAbortives;
    }
    /**
     * @return boolean
     */
    public function withNagashiMangan()
    {
        return $this->_withNagashiMangan;
    }
    /**
     * @return boolean
     */
    public function withAtamahane()
    {
        return $this->_withAtamahane;
    }
    /**
     * @return string
     */
    public function rulesetTitle()
    {
        return $this->_rulesetTitle;
    }
    /**
     * @return int
     */
    public function tenboDivider()
    {
        return $this->_tenboDivider;
    }
    /**
     * @return int
     */
    public function ratingDivider()
    {
        return $this->_ratingDivider;
    }
    /**
     * @return boolean
     */
    public function tonpuusen()
    {
        return $this->_tonpuusen;
    }
    /**
     * @return int
     */
    public function startRating()
    {
        return $this->_startRating;
    }
    /**
     * @return boolean
     */
    public function riichiGoesToWinner()
    {
        return $this->_riichiGoesToWinner;
    }
    /**
     * @return boolean
     */
    public function extraChomboPayments()
    {
        return $this->_extraChomboPayments;
    }
    /**
     * @return int
     */
    public function chomboPenalty()
    {
        return $this->_chomboPenalty;
    }
    /**
     * @return boolean
     */
    public function withKuitan()
    {
        return $this->_withKuitan;
    }
    /**
     * @return boolean
     */
    public function withButtobi()
    {
        return $this->_withButtobi;
    }
    /**
     * @return boolean
     */
    public function withMultiYakumans()
    {
        return $this->_withMultiYakumans;
    }
    /**
     * @return boolean
     */
    public function autoRegisterUsers()
    {
        return $this->_autoRegisterUsers;
    }
    /**
     * @return int
     */
    public function gameExpirationTime()
    {
        return $this->_gameExpirationTime;
    }
    /**
     * @return int
     */
    public function minPenalty()
    {
        return $this->_minPenalty;
    }
    /**
     * @return int
     */
    public function maxPenalty()
    {
        return $this->_maxPenalty;
    }
    /**
     * @return int
     */
    public function penaltyStep()
    {
        return $this->_penaltyStep;
    }
    /**
     * @return string
     */
    public function eventTitle()
    {
        return $this->_eventTitle;
    }
    /**
     * @return string
     */
    public function eventStatHost()
    {
        return $this->_eventStatHost;
    }
    /**
     * @return int
     */
    public function redZone()
    {
        return $this->_redZone;
    }
    /**
     * @return int
     */
    public function gameDuration()
    {
        return $this->_gameDuration;
    }
    /**
     * @return boolean
     */
    public function autoSeating()
    {
        return $this->_autoSeating;
    }
    /**
     * @return boolean
     */
    public function isOnline()
    {
        return $this->_isOnline;
    }
    /**
     * @return boolean
     */
    public function withLeadingDealerGameover()
    {
        return $this->_withLeadingDealerGameover;
    }
}
