<?php

namespace OrangeHive\Simplyment\Form\Element;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;

class SimplymentPlaceholderElement extends AbstractFormElement
{

    public function render()
    {
        $result = $this->initializeResultArray();

        $parameters = $this->data['parameterArray']['fieldConf']['config']['parameters'];

        $message = $parameters['message'];
        $code = trim(nl2br(print_r($parameters['code'] ?? '', true)));

        if (!empty($code)) {
            $code = '<code style="margin-left: 0;">' . $code . '</code>';
        }

        $html = <<<HTML
<div class="alert alert-warning">
<p>{$message}</p>
{$code}
</div>
HTML;

        $result['html'] = $html;

        return $result;
    }

}