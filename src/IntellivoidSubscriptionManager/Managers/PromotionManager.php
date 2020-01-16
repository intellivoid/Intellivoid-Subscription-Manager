<?php


    namespace IntellivoidSubscriptionManager\Managers;


    use IntellivoidSubscriptionManager\IntellivoidSubscriptionManager;

    /**
     * Class PromotionManager
     * @package IntellivoidSubscriptionManager\Managers
     */
    class PromotionManager
    {
        /**
         * @var IntellivoidSubscriptionManager
         */
        private $intellivoidSubscriptionManager;

        /**
         * PromotionManager constructor.
         * @param IntellivoidSubscriptionManager $intellivoidSubscriptionManager
         */
        public function __construct(IntellivoidSubscriptionManager $intellivoidSubscriptionManager)
        {
            $this->intellivoidSubscriptionManager = $intellivoidSubscriptionManager;
        }
    }