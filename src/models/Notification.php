<?php
class Notification extends PHPFrame_PersistentObject
{
    public function __construct(array $options=null)
    {
        // Some example fields...
        // Add fields before calling parent's constructor
        $this->addField(
            "title",
            null,
            false,
            new PHPFrame_StringFilter(array("min_length"=>0, "max_length"=>50))
        );
        $this->addField(
            "body",
            null,
            true,
            new PHPFrame_StringFilter(array("min_length"=>0, "max_length"=>140))
        );
        $this->addField(
            "type",
            "info",
            false,
            new PHPFrame_EnumFilter(array("enums"=>array(
                "error",
                "warning",
                "notice",
                "info",
                "success"
            )))
        );
        $this->addField(
            "sticky",
            false,
            false,
            new PHPFrame_BoolFilter()
        );

        parent::__construct($options);
    }
}
