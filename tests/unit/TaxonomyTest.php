<?php

use nkostadinov\taxonomy\models\TaxonomyDef;
use nkostadinov\taxonomy\models\TaxonomyTerms;
use nkostadinov\taxonomy\tests\models\SampleTable;
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
        $object_id = 2;
        //1. Add TAG taxonomy
        $term = new nkostadinov\taxonomy\models\TaxonomyDef();
        $term->name = 'test-tag';
        $term->class = nkostadinov\taxonomy\components\terms\TagTerm::className();
        $term->data_table = 'sample_tags';
        $term->ref_table = SampleTable::className();
        $this->tester->assertTrue($term->save());

        //2. Create data table
        $this->tester->assertFalse($this->getTaxnonomy()->getTerm($term->name)->isInstalled(), 'The term should NOT be installed.');
        $this->getTaxnonomy()->getTerm($term->name)->install();
        $this->tester->assertTrue($this->getTaxnonomy()->getTerm($term->name)->isInstalled(), 'The term should be installed.');

        //3. Add some data
        $this->getTaxnonomy()->addTerm($term->name, $object_id, ['tag1', 'tag2']);
        $term->refresh();
        //check count on term
        $this->tester->assertEquals(2, $term->total_count, 'Tag term count not correct!');

        $data = $this->getTaxnonomy()->getTerms($term->name, $object_id); // tag1 + tag2
        $this->tester->assertEquals(2, count($data), 'Tag term count not correct!');
        $this->tester->assertContains('tag1', $data, 'Tag1 missing in data');
        $this->tester->assertContains('tag2', $data, 'Tag1 missing in data');

        $this->getTaxnonomy()->removeTerm($term->name, $object_id, ['name' => 'tag1']);
        $data = $this->getTaxnonomy()->getTerms($term->name, $object_id); // tag1 + tag2
        $this->tester->assertEquals(1, count($data), 'Tag term count not correct!');
        $this->tester->assertNotContains('tag1', $data, 'Tag1 present in data');
        $this->tester->assertContains('tag2', $data, 'Tag1 missing in data');

        $this->getTaxnonomy()->removeTerm($term->name, $object_id);
        $data = $this->getTaxnonomy()->getTerms($term->name, $object_id); // tag1 + tag2
        $this->tester->assertEmpty($data, 'Tag term data not correct!');
        $this->tester->assertNotContains('tag1', $data, 'Tag1 present in data');
    }

    public function testProperties()
    {
        $object_id = 3;
        //1. Add TAG taxonomy
        $term = new nkostadinov\taxonomy\models\TaxonomyDef();
        $term->name = 'test-property';
        $term->class = nkostadinov\taxonomy\components\terms\PropertyTerm::className();
        $term->data_table = 'sample_property';
        $term->ref_table = SampleTable::className();
        $this->tester->assertTrue($term->save());

        //2. Create data table
        $this->tester->assertFalse($this->getTaxnonomy()->getTerm($term->name)->isInstalled(), 'The term should NOT be installed.');
        $this->getTaxnonomy()->getTerm($term->name)->install();
        $this->tester->assertTrue($this->getTaxnonomy()->getTerm($term->name)->isInstalled(), 'The term should be installed.');

        //3. Add some data
        $this->getTaxnonomy()->addTerm($term->name, $object_id, ['prop1' => 'value1', 'prop2' => 'value2']);
        $term->refresh();
        //check count on term
        $this->tester->assertEquals(2, $term->total_count, 'Tag term count not correct!');

        //check update of field
        $this->getTaxnonomy()->addTerm($term->name, $object_id, ['prop1' => 'value1_update']);
        $data = $this->getTaxnonomy()->getTerms($term->name, $object_id);
        $this->tester->assertEquals('value1_update', $data['prop1'], 'Property value not updated');

        $data = SampleTable::find()->hasTags()->all();
    }
}