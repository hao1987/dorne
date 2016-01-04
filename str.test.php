<?php

class StrTest extends PHPUnit_Framework_TestCase
{
    // public function setUp()
    // {
    // $this->container = new Str;
    // }

    /**
     * @param string $originalString String to be sluggified
     * @param string $expectedResult What we expect our slug result to be
     *
     * @dataProvider strProvider
     */
    public function testSlug($originalString, $expectedString)
    {
        $result = str::slug($originalString);
        $this->assertEquals($expectedString, $result);
    }

    public function strProvider()
    {
        return array(
            array('This string will be sluggified', 'this-string-will-be-sluggified'),
            array('THIS STRING WILL BE SLUGGIFIED', 'this-string-will-be-sluggified'),
            array('This1 string2 will3 be 44 sluggified10', 'this1-string2-will3-be-44-sluggified10'),
            array('This! @string#$ %$will ()be "sluggified', 'this-string-will-be-sluggified'),
            // array("Tänk efter nu – förr'n vi föser dig bort", 'tank-efter-nu-forrn-vi-foser-dig-bort'),         
            array('', 1)
        );
    }
}