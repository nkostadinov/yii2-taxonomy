<?php

use nkostadinov\taxonomy\models\TaxonomyDef;
use nkostadinov\taxonomy\models\TaxonomyTerms;
use yii\codeception\TestCase;

class TaxonomyTest extends TestCase
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public $appConfig = '@tests/tests/config/unit.php';

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    /**
     * @return nkostadinov\taxonomy\Taxonomy
     */
    protected function getTaxnonomy()
    {
        return Yii::$app->taxonomy;
    }

    // tests
    public function testInstall()
    {
        //just to check that the app instance is correct
         $this->assertTrue(Yii::$app->id == 'Yii2 Taxonomy Test');
        //the table does not exists ... yet
        $this->tester->assertFalse($this->getTaxnonomy()->isInstalled(), 'Tables exist before install!');
        //perform install
        $this->getTaxnonomy()->install();
        //check installed ?
        $this->tester->assertTrue($this->getTaxnonomy()->isInstalled(), 'Missing tables! ');
    }

    /**
     * @depends testInstall
     */
    public function testTags()
    {
        //perform test
        //1. Add TAG taxonomy
        $term = new nkostadinov\taxonomy\models\TaxonomyDef();
        $term->name = 'test-tag';
        $term->class = nkostadinov\taxonomy\components\terms\TagTerm::className();
        $term->data_table = 'sample_tags';
        $term->ref_table = nkostadinov\taxonomy\models\SampleTable::className();
        $this->assertTrue($term->save());
        //2. Create data table
        $tax = $this->getTaxnonomy();
        $tax->getTerm($term->name)->install();
        $this->assertTrue($tax->isInstalled());
        //3. Add some data
        $tax->addTerm($term->name, 2, ['tag1', 'tag2']);
        $term->refresh();
        //check count on term
        $this->tester->assertEquals(2, $term->total_count, 'Tag term count not correct!');

        $data = $tax->getTerms($term->name, 1);//empty
        $this->tester->assertEmpty($data);

        $data = $tax->getTerms($term->name, 2); // tag1 + tag2
        $this->tester->assertEquals(2, count($data), 'Tag term count not correct!');
        $this->tester->assertContains('tag1', $data, 'Tag1 missing in data');
        $this->tester->assertContains('tag2', $data, 'Tag1 missing in data');

        $tax->removeTerm($term->name, 2, ['name' => 'tag1']);
        $data = $tax->getTerms($term->name, 2); // tag1 + tag2
        $this->tester->assertEquals(1, count($data), 'Tag term count not correct!');
        $this->tester->assertNotContains('tag1', $data, 'Tag1 present in data');
        $this->tester->assertContains('tag2', $data, 'Tag1 missing in data');

        $tax->removeTerm($term->name, 2);
        $data = $tax->getTerms($term->name, 2); // tag1 + tag2
        $this->tester->assertEmpty($data, 'Tag term data not correct!');
        $this->tester->assertNotContains('tag1', $data, 'Tag1 present in data');

    }
}