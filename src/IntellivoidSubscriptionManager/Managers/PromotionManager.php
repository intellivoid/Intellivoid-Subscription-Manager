<?php


    namespace IntellivoidSubscriptionManager\Managers;


    use IntellivoidSubscriptionManager\Abstracts\SearchMethods\SubscriptionPromotionSearchMethod;
    use IntellivoidSubscriptionManager\Exceptions\DatabaseException;
    use IntellivoidSubscriptionManager\Exceptions\InvalidSearchMethodException;
    use IntellivoidSubscriptionManager\Exceptions\InvalidSubscriptionPromotionNameException;
    use IntellivoidSubscriptionManager\Exceptions\SubscriptionPromotionNotFoundException;
    use IntellivoidSubscriptionManager\IntellivoidSubscriptionManager;
    use IntellivoidSubscriptionManager\Objects\SubscriptionPromotion;
    use IntellivoidSubscriptionManager\Utilities\Converter;
    use IntellivoidSubscriptionManager\Utilities\Validate;
    use msqg\QueryBuilder;
    use ZiProto\ZiProto;

    /**
     * Class PromotionManager
     * @package IntellivoidSubscriptionManager\Managers
     * @noinspection PhpUnused
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



        /**
         * Gets an existing Subscription Promotion from the database
         *
         * @param string $search_method
         * @param string $value
         * @return SubscriptionPromotion
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws InvalidSubscriptionPromotionNameException
         * @throws SubscriptionPromotionNotFoundException
         * @noinspection PhpUnused
         */
        public function getSubscriptionPromotion(string $search_method, string $value): SubscriptionPromotion
        {
            switch($search_method)
            {
                case SubscriptionPromotionSearchMethod::byId:
                    $search_method = $this->intellivoidSubscriptionManager->getDatabase()->real_escape_string($search_method);
                    $value = (int)$value;
                    break;

                case SubscriptionPromotionSearchMethod::byPublicId:
                    $search_method = $this->intellivoidSubscriptionManager->getDatabase()->real_escape_string($search_method);
                    $value = $this->intellivoidSubscriptionManager->getDatabase()->real_escape_string($value);
                    break;

                case SubscriptionPromotionSearchMethod::byPromotionCode:
                    if(Validate::subscriptionPromotionCode($value) == false)
                    {
                        throw new InvalidSubscriptionPromotionNameException();
                    }
                    $search_method = $this->intellivoidSubscriptionManager->getDatabase()->real_escape_string($search_method);
                    $value = Converter::subscriptionPromotionCode($value);
                    $value = $this->intellivoidSubscriptionManager->getDatabase()->real_escape_string($value);
                    break;

                default:
                    throw new InvalidSearchMethodException();
            }

            $Query = QueryBuilder::select('subscription_promotions', [
                'id',
                'public_id',
                'promotion_code',
                'subscription_plan_id',
                'initial_price',
                'cycle_price',
                'affiliation_account_id',
                'affiliation_initial_share',
                'affiliation_cycle_share',
                'features',
                'status',
                'flags',
                'last_updated_timestamp',
                'created_timestamp'
            ], $search_method, $value);
            $QueryResults = $this->intellivoidSubscriptionManager->getDatabase()->query($Query);

            if($QueryResults == false)
            {
                throw new DatabaseException($Query, $this->intellivoidSubscriptionManager->getDatabase()->error);
            }
            else
            {
                if($QueryResults->num_rows !== 1)
                {
                    throw new SubscriptionPromotionNotFoundException();
                }

                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);
                $Row['features'] = ZiProto::decode($Row['features']);
                $Row['flags'] = ZiProto::decode($Row['flags']);
                return SubscriptionPromotion::fromArray($Row);
            }
        }
    }