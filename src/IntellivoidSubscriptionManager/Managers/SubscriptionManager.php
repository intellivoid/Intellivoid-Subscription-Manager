<?php


    namespace IntellivoidSubscriptionManager\Managers;


    use IntellivoidSubscriptionManager\Abstracts\SearchMethods\SubscriptionSearchMethod;
    use IntellivoidSubscriptionManager\Exceptions\DatabaseException;
    use IntellivoidSubscriptionManager\Exceptions\InvalidSearchMethodException;
    use IntellivoidSubscriptionManager\Exceptions\SubscriptionNotFoundException;
    use IntellivoidSubscriptionManager\IntellivoidSubscriptionManager;
    use IntellivoidSubscriptionManager\Objects\Subscription;
    use msqg\QueryBuilder;
    use ZiProto\ZiProto;

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

        /**
         * Gets an existing subscription from the database
         *
         * @param string $search_method
         * @param string $value
         * @return Subscription
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws SubscriptionNotFoundException
         */
        public function getSubscription(string $search_method, string $value): Subscription
        {
            switch($search_method)
            {
                case SubscriptionSearchMethod::byId:
                    $search_method = $this->intellivoidSubscriptionManager->getDatabase()->real_escape_string($search_method);
                    $value = (int)$value;
                    break;

                case SubscriptionSearchMethod::byPublicId:
                    $search_method = $this->intellivoidSubscriptionManager->getDatabase()->real_escape_string($search_method);
                    $value = $this->intellivoidSubscriptionManager->getDatabase()->real_escape_string($value);
                    break;

                default:
                    throw new InvalidSearchMethodException();
            }

            $Query = QueryBuilder::select('subscriptions', [
                'id',
                'public_id',
                'account_id',
                'subscription_plan_id',
                'active',
                'billing_cycle',
                'next_billing_cycle',
                'properties',
                'created_timestamp',
                'flags'
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
                    throw new SubscriptionNotFoundException();
                }

                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);
                $Row['flags'] = ZiProto::decode($Row['flags']);
                return Subscription::fromArray($Row);
            }
        }

        /**
         * Updates an existing subscription to the database
         *
         * @param Subscription $subscription
         * @return bool
         * @throws DatabaseException
         */
        public function updateSubscription(Subscription $subscription): bool
        {
            $id = (int)$subscription->ID;
            $active = (int)$subscription->Active;
            $billing_cycle = (int)$subscription->BillingCycle;
            $next_billing_cycle = (int)$subscription->NextBillingCycle;
            $properties = ZiProto::encode($subscription->Properties->toArray());
            $properties = $this->intellivoidSubscriptionManager->getDatabase()->real_escape_string($properties);
            $flags = ZiProto::encode($subscription->Flags);
            $flags = $this->intellivoidSubscriptionManager->getDatabase()->real_escape_string($flags);

            $Query = QueryBuilder::update('subscriptions',array(
                'active' => $active,
                'billing_cycle' => $billing_cycle,
                'next_billing_cycle' => $next_billing_cycle,
                'properties' => $properties,
                'flags' => $flags
            ), 'id', $id);
            $QueryResults = $this->intellivoidSubscriptionManager->getDatabase()->query($Query);

            if($QueryResults == true)
            {
                return true;
            }
            else
            {
                throw new DatabaseException($Query, $this->intellivoidSubscriptionManager->getDatabase()->error);
            }
        }
    }