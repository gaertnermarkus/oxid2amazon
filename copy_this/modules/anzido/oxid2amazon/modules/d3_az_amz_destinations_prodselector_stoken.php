<?PHP

class d3_az_amz_destinations_prodselector_stoken extends d3_az_amz_destinations_prodselector_stoken_parent
{

    public function render()
    {
        $ret = parent::render();
        $this->_aViewData["sAddData"] = $this->getSession()->getSessionChallengeToken();

        return $ret;
    }

}