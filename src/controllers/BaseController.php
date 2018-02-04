<?php
namespace Usedesk\SyncEngineIntegration\Controllers;
class BaseController extends Controller {

    /**
     * @var null|Company
     */
    protected $CurrentCompany = null;
    /**
     * @var null|User
     */
    protected $CurrentUser = null;

    public function __construct()
    {
        $this->CurrentCompany = Company::current();
        $this->CurrentUser = Auth::user()->user();
        View::share([
            'CurrentCompany' => $this->CurrentCompany,
            'CurrentUser' => $this->CurrentUser,
        ]);
    }

    protected function setupLayout() {
        if (!is_null($this->layout)) {
            $this->layout = View::make($this->layout);
        }
    }

    /**
     * @return array
     */
    public static function getUserNames() {
        $users = User::where('company_id', '=', Company::current()->id)->lists('name', 'id');
        $result = [];
        foreach ($users as $id => $name) {
            $result[] = [
                'id' => $id,
                'name' => $name,
            ];
        }
        return $result;
    }

    protected function getMainView($name, $params = [])
    {
        if ($this->CurrentCompany) {
            $additionalParams = [];
            $additionalParams['userNames'] = BaseController::getUserNames();
            $additionalParams['editorVariables'] = TicketVariable::getCompanyKeyNameList($this->CurrentCompany->id);

            $additionalParams['glarvedIntegrationExists'] = $this->checkIntegrationByKey(Integration::TYPE_GLAVRED, $this->CurrentCompany->id);
            $additionalParams['spellerIntegrationExists'] = $this->checkIntegrationByKey(Integration::TYPE_SPELLER, $this->CurrentCompany->id);
            $additionalParams['chatIntegrationExists'] = $this->checkIntegrationByKey(Integration::TYPE_CHAT, $this->CurrentCompany->id);
            $additionalParams['supportIntegrationExists'] = $this->checkIntegrationByKey(Integration::TYPE_SUPPORT, $this->CurrentCompany->id);
            $additionalParams['slaIntegrationExists'] = $this->checkIntegrationByKey(Integration::TYPE_SLA, $this->CurrentCompany->id);
            $additionalParams['csiIntegrationExists'] = $this->checkIntegrationByKey(Integration::TYPE_CSI, $this->CurrentCompany->id);
            $additionalParams['aiIntegrationExists'] = $this->checkIntegrationByKey(Integration::TYPE_AI, $this->CurrentCompany->id);
            $additionalParams['afIntegrationExists'] = $this->checkIntegrationByKey(Integration::TYPE_ADDITIONAL_FIELDS, $this->CurrentCompany->id);
            $additionalParams['monitoringIntegrationExists'] = $this->checkIntegrationByKey(Integration::TYPE_MONITOR, $this->CurrentCompany->id);

            $additionalParams['monitoringEnableAdmin'] = Monitoring::where('company_id', '=', $this->CurrentCompany->id)->first()->enable_admin;

            $additionalParams['chatOperatorStatus'] = User_ChatApiController::checkOperatorStatus();
            $additionalParams['hasGetStarted'] = $this->CurrentCompany->hasGetStarted();
            $additionalParams['getStartedIsCompleted'] = Company::getStartedComplete();
            $additionalParams['userToken'] = User_ChatApiController::getUserToken();
            $additionalParams['carbonTodayFormatted'] = \Carbon\Carbon::today()->format('Y-m-d');

            $additionalParams['currentUserPermissions'] = [];
            $additionalParams['currentUserPermissions']['clients'] = $this->CurrentUser->checkPermission(UserGroupPermission::PERMISSION_CLIENTS);
            $additionalParams['currentUserPermissions']['support'] = $this->CurrentUser->checkPermission(UserGroupPermission::PERMISSION_SUPPORT);
            $additionalParams['currentUserPermissions']['reports'] = $this->CurrentUser->checkPermission(UserGroupPermission::PERMISSION_REPORTS);
            $additionalParams['currentUserPermissions']['channels'] = $this->CurrentUser->checkPermission(UserGroupPermission::PERMISSION_CHANNELS);
            $additionalParams['currentUserPermissions']['blocks'] = $this->CurrentUser->checkPermission(UserGroupPermission::PERMISSION_BLOCKS);
            $additionalParams['currentUserPermissions']['macroses'] = $this->CurrentUser->checkPermission(UserGroupPermission::PERMISSION_MACROSES);
            $additionalParams['currentUserPermissions']['triggers'] = $this->CurrentUser->checkPermission(UserGroupPermission::PERMISSION_TRIGGERS);
            $additionalParams['currentUserPermissions']['settings'] = $this->CurrentUser->checkPermission(UserGroupPermission::PERMISSION_SETTINGS);

            $params = array_merge($params, $additionalParams);

        }
        return View::make($name, $params);
    }


    /**
     * Метод для проверки активности интеграции
     * @param string $integrationKey
     * @param int $companyId
     * @return bool
     */
    protected function checkIntegrationByKey($integrationKey, $companyId)
    {
        $integrationId = Integration::getIntegrationId($integrationKey);
        if ($integrationId) {
            return CompanyIntegration::where('company_id', '=', $companyId)
                ->where('integration_id', '=', $integrationId)
                ->where('status', '=', true)
                ->exists();
        }

        return false;
    }

    /**
     * @param Company $company
     * @return bool
     */
    protected function companyIsTrial(Company $company) {
        return $company->isTrial();
    }

    /**
     * @param Company $company
     * @return bool
     */
    protected function companyIsBlocked(Company $company) {
        return $company->billing->data_plan != CompanyBilling::DATA_PLAN_PAID;
    }

    /**
     * Получаем список возможных соединений для канала
     * @param Company $company
     * @param bool $incoming
     * @param string|bool $internal_email
     * @return array
     */
    protected function getCompanyEmailChannelConnectionList(Company $company, $incoming = true, $internal_email = false) {
        return CompanyEmailChannel::getConnectionLabelList($company ,$incoming, $internal_email);
    }

    /**
     * @param Ticket $ticket
     * @return string
     */
    protected function getAssigneeNameFromTicket(Ticket $ticket)
    {
        $name = '';
        if ($ticket->assignee_id) {
            $name = User::select('name')->where('id', $ticket->assignee_id)->first()->name;
        }
        return $name;
    }

    /**
     * @param Ticket $ticket
     * @return string
     */
    protected function getGroupNameFromTicket(Ticket $ticket)
    {
        $name = '';
        if ($ticket->group) {
            $name = UserGroup::select('name')->where('id', $ticket->group)->first()->name;
        }
        return $name;
    }

    /**
     * @param int $userId
     * @return bool
     */
    protected function userIsNotDeleted($userId) {
        return User::where('id', $userId)->where('deleted', true)->exists();
    }

    /**
     * @param int $groupId
     * @return bool
     */
    protected function groupIsNotDeleted($groupId) {
        return UserGroup::where('id', $groupId)->where('deleted', true)->exists();
    }

    /**
     * Возвращает view для DataTables
     * @param Ticket $ticket
     * @return string
     */
    protected function getTicketSubjectDataTablesView(Ticket $ticket) {
        return View::make('user.tickets.include.subject', ['ticket' => $ticket])->render();
    }


    /**
     * Возвращает view для DataTables
     * @param Ticket $ticket
     * @return string
     */
    protected function getTicketAssigneeDataTablesView(Ticket $ticket) {
        $user = null;
        if ($ticket->assignee_id) {
            $user = User::select('id', 'name', 'deleted')->where('id', $ticket->assignee_id)->remember(1)->first();
        }
        $group = null;
        if ($ticket->group) {
            $group = UserGroup::select('id', 'name', 'deleted')->where('id', $ticket->group)->remember(1)->first();
        }
        return View::make('user.tickets.include.assignee', [
            'assignee' => $user,
            'group' => $group,
        ])->render();
    }

    /**
     * @param string $key
     * @return array
     */
    protected function explodeInput($key)
    {
        return TagsHelper::explode(Input::get($key));
    }

}
