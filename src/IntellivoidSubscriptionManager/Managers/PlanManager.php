<?php /** @noinspection PhpUnused */


    namespace IntellivoidSubscriptionManager\Managers;


    use IntellivoidSubscriptionManager\Abstracts\SearchMethods\SubscriptionPlanSearchMethod;
    use IntellivoidSubscriptionManager\Exceptions\DatabaseException;
    use IntellivoidSubscriptionManager\Exceptions\InvalidSearchMethodException;
    use IntellivoidSubscriptionManager\Exceptions\SubscriptionPlanNotFoundException;
    use IntellivoidSubscriptionManager\IntellivoidSubscriptionManager;
    use IntellivoidSubscriptionManager\Objects\SubscriptionPlan;
    use msqg\QueryBuilder;
    use ZiProto\ZiProto;

    /**
     * Class PlanManager
     * @package IntellivoidSubscriptionManager\Managers
     */
    class PlanManager
    {
        /**
         * @var IntellivoidSubscriptionManager
         */
        private $intellivoidSubscriptionManager;

        /**
         * PlanManager constructor.
         * @param IntellivoidSubscriptionManager $intellivoidSubscriptionManager
         */
        public function __construct(IntellivoidSubscriptionManager $intellivoidSubscriptionManager)
        {
            $this->intellivoidSubscriptionManager = $intellivoidSubscriptionManager;
        }

        /**
         * Returns a Subscription Plan from the database
         *
         * @param string $search_method
         * @param string $value
         * @return SubscriptionPlan
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws SubscriptionPlanNotFoundException
         * @noinspection DuplicatedCode
         */
        public function getSubscriptionPlan(string $search_method, string $value): SubscriptionPlan
        {
            switch($search_method)
            {
                case SubscriptionPlanSearchMethod::byId:
                    $search_method = $this->intellivoidSubscriptionManager->getDatabase()->real_escape_string($search_method);
                    $value = (int)$value;
                    break;

                case SubscriptionPlanSearchMethod::byPublicId:
                    $search_method = $this->intellivoidSubscriptionManager->getDatabase()->real_escape_string($search_method);
                    $value = $this->intellivoidSubscriptionManager->getDatabase()->real_escape_string($value);
                    break;

                default:
                    throw new InvalidSearchMethodException();
            }

            $Query = QueryBuilder::select('subscription_plans', [
                'id',
                'public_id',
                'application_id',
                'plan_name',
                'features',
                'initial_price',
                'cycle_price',
                'billing_cycle',
                'status',
                'flags',
                'last_updated',
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
                    throw new SubscriptionPlanNotFoundException();
                }

                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);
                $Row['features'] = ZiProto::decode($Row['features']);
                $Row['flags'] = ZiProto::decode($Row['flags']);
                return SubscriptionPlan::fromArray($Row);
            }
        }

        /**
         * Fetches a Subscription Plan by a Plan Name
         *
         * @param int $application_id
         * @param string $name
         * @return SubscriptionPlan
         * @throws DatabaseException
         * @throws SubscriptionPlanNotFoundException
         * @noinspection DuplicatedCode
         */
        public function getSubscriptionPlanByName(int $application_id, string $name): SubscriptionPlan
        {
            $application_id = (int)$application_id;
            $name = $this->intellivoidSubscriptionManager->getDatabase()->real_escape_string($name);

            $Query = QueryBuilder::select('subscription_plans', [
                'id',
                'public_id',
                'application_id',
                'plan_name',
                'features',
                'initial_price',
                'cycle_price',
                'billing_cycle',
                'status',
                'flags',
                'last_updated',
                'created_timestamp'
            ], 'application_id', $application_id . "' AND plan_name='$name");
            $QueryResults = $this->intellivoidSubscriptionManager->getDatabase()->query($Query);

            if($QueryResults == false)
            {
                throw new DatabaseException($Query, $this->intellivoidSubscriptionManager->getDatabase()->error);
            }
            else
            {
                if($QueryResults->num_rows !== 1)
                {
                    throw new SubscriptionPlanNotFoundException();
                }

                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);
                $Row['features'] = ZiProto::decode($Row['features']);
                $Row['flags'] = ZiProto::decode($Row['flags']);
                return SubscriptionPlan::fromArray($Row);
            }
        }
    }