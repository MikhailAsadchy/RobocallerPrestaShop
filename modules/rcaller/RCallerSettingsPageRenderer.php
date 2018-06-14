<?php

class RCallerSettingsPageRenderer
{
    const SETTINGS_FORM_USERNAME = "rcaller_username";
    const SETTINGS_FORM_PASSWORD = "rcaller_password";

    /**
     * @return string
     */
    public function render_settings_page()
    {
        $checkCredentialsStatus = "";

        if ($this->isPostMethod() && $this->shouldHandlePost()) {
            if ($this->isCheckCredentialsRequest()) {
                $checkCredentialsStatus = $this->doCheckCredentials();
                if ($checkCredentialsStatus === "success") {
                    $this->doSaveSettings();
                }
            } else if ($this->isSaveSettingsRequest()) {
                $this->doSaveSettings();
            }
        }

        $username = Configuration::get(RCallerConstants::USERNAME_CONFIG_KEY);
        $password = Configuration::get(RCallerConstants::PASSWORD_CONFIG_KEY);

        return $this->renderSettingsPage($checkCredentialsStatus, $username, $password);
    }

    public static function saveSettings($userName, $password)
    {
        Configuration::updateValue(RCallerConstants::USERNAME_CONFIG_KEY, $userName);
        Configuration::updateValue(RCallerConstants::PASSWORD_CONFIG_KEY, $password);
    }

    public static function checkCredentials($userName, $password)
    {
        $response = RCallerSender::checkRCallerCredentials($userName, $password);
        return self::processResponse($response);
    }

    /**
     * @param $httpCode
     * @return string
     */
    private static function processResponse($httpCode)
    {
        if ($httpCode === 200) {
            $checkCredentialsResult = "success";
        } else if ($httpCode === 401) {
            $checkCredentialsResult = "bad credentials";
        } else if ($httpCode == 403) {
            $checkCredentialsResult = "You have negative balance, so the requests to rcaller will not be sent";
        } else {
            $checkCredentialsResult = "unknown error";
        }
        return $checkCredentialsResult;
    }


    /**
     * @param $checkCredentialsStatus
     * @param $username
     * @param $password
     * @return string
     */
    private function renderSettingsPage($checkCredentialsStatus, $username, $password)
    {
        return $this->renderSettingsTitle() . $this->renderSettingsForm($username, $password) . $this->renderCheckCredentialsStatus($checkCredentialsStatus);
    }

    /**
     * @param $checkCredentialsStatus
     * @return string
     */
    private function renderCheckCredentialsStatus($checkCredentialsStatus)
    {
        if (!empty($checkCredentialsStatus)) {
            return "<div>RCaller credentials status: " . $checkCredentialsStatus . "</div>";
        } else {
            return "";
        }
    }

    /**
     * @param $username
     * @param $password
     * @return string
     */
    private function renderSettingsForm($username, $password)
    {
        return "
    <form method=\"post\">

        <input name=\"" . self::SETTINGS_FORM_USERNAME . "\" type=\"text\" size=\"25\"
               value=\"" . $username . "\">
        <input name=\"" . self::SETTINGS_FORM_PASSWORD . "\" type=\"password\" size=\"25\"
               value=\"" . $password . "\">

        <input type=\"submit\" name=\"checkCredentials\" value=\"Check credentials\">
        <input type=\"submit\" name=\"save\" value=\"Save\">

    </form> 
    ";
    }

    /**
     * @return string
     */
    private function renderSettingsTitle()
    {
        return "<div>Configure RCaller credentials</div>";
    }


    /**
     * @return mixed
     */
    function isCheckCredentialsRequest()
    {
        return $_POST["checkCredentials"];
    }

    /**
     * @return mixed
     */
    function isSaveSettingsRequest()
    {
        return $_POST["save"];
    }

    /**
     * @return bool
     */
    function shouldHandlePost()
    {
        return $this->isCheckCredentialsRequest() || $this->isSaveSettingsRequest();
    }

    /**
     * @return bool
     */
    function isPostMethod()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    function doCheckCredentials()
    {
        $userName = $_POST[self::SETTINGS_FORM_USERNAME];
        $password = $_POST[self::SETTINGS_FORM_PASSWORD];
        return RCallerSettingsPageRenderer::checkCredentials($userName, $password);
    }

    function doSaveSettings()
    {
        $userName = $_POST[self::SETTINGS_FORM_USERNAME];
        $password = $_POST[self::SETTINGS_FORM_PASSWORD];
        RCallerSettingsPageRenderer::saveSettings($userName, $password);
    }


}