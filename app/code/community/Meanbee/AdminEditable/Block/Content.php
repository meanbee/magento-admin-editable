<?php

/**
 * Class Meanbee_AdminEditable_Block_Content
 *
 * @method integer getStaticBlockId()
 * @method string getStaticBlockTitle()
 * @method $this setStaticBlockId(integer)
 * @method $this setStaticBlockTitle(string)
 * @method string setStaticBlockDefaultTemplate(string)
 */
class Meanbee_AdminEditable_Block_Content extends Mage_Core_Block_Abstract {

    protected $_errors = array();

    /**
     * @return Mage_Cms_Block_Block|false
     */
    public function getStaticBlock() {
        if (!$this->_doesStaticBlockExist()) {
            if ($this->_hasRequiredParameters()) {
                $this->_log(sprintf("The block %s does not exist, creating block.", $this->getStaticBlockId()));
                $this->_createStaticBlock();
            } else {
                $this->_errors[] = sprintf("The block %s does not exist, required parameters not provided, not creating block.", $this->getStaticBlockId());
                return false;
            }
        }

        $block = $this->getLayout()->createBlock('cms/block')
            ->setBlockId($this->getStaticBlockId());

        return $block;
    }

    /**
     * @return string
     */
    public function getStaticBlockDefaultTemplate() {
        $key = 'static_block_default_template';
        if (!$this->hasData($key)) {
            $candidate_template = sprintf("static_blocks/%s.phtml", $this->getStaticBlockId());
        } else {
            $candidate_template = $this->getData($key);
        }
        $template_file = Mage::getDesign()->getTemplateFilename($candidate_template, array(
            '_relative' => true
        ));

        $template_file = Mage::getBaseDir('design') . DIRECTORY_SEPARATOR . $template_file;
        $this->setData($key, $template_file);

        return $this->getData($key);
    }

    /**
     * @return string
     */
    public function getStaticBlockDefaultContent() {
        $key = 'static_block_default_content';
        if (!$this->hasData($key)) {
            $file_name = $this->getStaticBlockDefaultTemplate();
            $html = sprintf("This content is retrieved from the %s static block in the administration area.", $this->getStaticBlockId());
            if (file_exists($file_name) && is_readable($file_name)) {
                $html = file_get_contents($file_name);
            }

            $this->setData($key, $html);
        }

        return $this->getData($key);
    }

    /**
     * Check to see if the static block exists in the database.
     *
     * There could be multiple definitions of the block for different stores, so check that it's not zero instead of
     * checking that it's one.
     *
     * @return boolean
     */
    protected function _doesStaticBlockExist() {
        return Mage::getModel('cms/block')->getCollection()
            ->addFieldToFilter('identifier', $this->getStaticBlockId())
            ->count() != 0;
    }

    /**
     * Force creation of the static block in the database.
     *
     * We need to hope into the admin store scope, but reset it afterwards.
     *
     * return Mage_Cms_Block_Block
     */
    protected function _createStaticBlock() {
        $storeId = Mage::app()->getStore()->getId();

        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        $args = array(
            'identifier'    => $this->getStaticBlockId(),
            'title'         => $this->getStaticBlockTitle(),
            'content'       => $this->getStaticBlockDefaultContent(),
            'is_active'     => 1,
            'stores'        => array(0)
        );

        $block = Mage::getModel('cms/block')->setData($args)->save();

        Mage::app()->setCurrentStore($storeId);

        return $block;
    }

    /**
     * @return bool
     */
    protected function _hasRequiredParameters() {
        $required_params = array(
            'static_block_id',
            'static_block_title'
        );

        foreach ($required_params as $required_param) {
            if (!$this->hasData($required_param)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return string
     */
    protected function _toHtml() {
        $block = $this->getStaticBlock();

        if ($block) {
            return $block->toHtml();
        } else {
            $message = sprintf(
                "Unable to render %s: %s.",
                $this->getStaticBlockId(),
                join(', ', $this->_errors)
            );

            $this->_log($message);
            return $message;
        }
    }

    /**
     * @param $message
     * @param int $level
     */
    protected function _log($message, $level = Zend_Log::DEBUG) {
        Mage::log(sprintf("[%s] %s", __CLASS__, $message), $level, 'meanbee_admineditable.log');
    }
}
