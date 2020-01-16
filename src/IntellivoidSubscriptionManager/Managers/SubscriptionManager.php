<?php


    namespace IntellivoidSubscriptionManager\Managers;


    use IntellivoidSubscriptionManager\IntellivoidSubscriptionManager;

    /**
     * Class SubscriptionManager
     * @package IntellivoidSubscriptionManager\Managers
     */
    class SubscriptionManager
    {
        /**
         * @var IntellivoidSubscriptionManager
         */
        private $intellivoidSubscriptionManager;

        /**
         * SubscriptionManager constructor.
         * @param IntellivoidSubscriptionManager $intellivoidSubscriptionManager
         */
        public function __construct(IntellivoidSubscriptionManager $intellivoidSubscriptionManager)
        {
            $this->intellivoidSubscriptionManager = $intellivoidSubscriptionManager;
        }
    }