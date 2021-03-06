<?php

use nkostadinov\taxonomy\components\terms\CategoryTerm;
use nkostadinov\taxonomy\components\terms\PropertyTerm;
use nkostadinov\taxonomy\components\terms\TagTerm;
use nkostadinov\taxonomy\models\TaxonomyDef;
use nkostadinov\taxonomy\models\TaxonomyTerms;
use nkostadinov\taxonomy\Taxonomy;
use nkostadinov\taxonomy\tests\models\SampleTable;
use yii\codeception\TestCase;
use yii\console\controllers\MigrateController;


class Migrator extends MigrateController
{
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config); // TODO: Change the autogenerated stub
    }

    public function runSingle($class)
    {
        ob_start();
        $action = $this->createAction('up');
        if($this->beforeAction($action))
            $this->migrateUp($class);
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }

    public function remove($class)
    {
        if($this->beforeAction('down'))
            $this->migrateDown($class);
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }
}

class TaxonomyTest extends TestCase
{
    /**
     * @var UnitTester
     */
    protected $tester;

    public $appConfig = '@tests/tests/config/unit.php';

    private function runMigration($class)
    {
        $migrator = new Migrator('migrator', Yii::$app);
        $migrator->runSingle($class);
    }

    /**
     * @return Taxonomy
     */
    protected function getTaxonomy()
    {
        return Yii::$app->taxonomy;
    }

    // tests
    public function testInstall()
    {
        //just to check that the app instance is correct
         $this->assertTrue(Yii::$app->id == 'Yii2 Taxonomy Test');
        //check installed ?
        $this->tester->assertTrue($this->getTaxonomy()->isInstalled(), 'Missing tables! ');
    }

    /**
     * @depends testInstall
     */
    public function testTags()
    {
        $object_id = 2;
        //1. Add TAG taxonomy
        $term = new TaxonomyDef();
        $term->name = 'test_tag';
        $term->class = TagTerm::className();
        $term->data_table = 'sample_tags';
        $term->ref_table = SampleTable::className();

        //2. Create data table
        $tagTerm = Yii::createObject($term->attributes);
        $migration = $tagTerm->install();
        $this->runMigration($migration);
        $this->tester->assertTrue($this->getTaxonomy()->getTerm($term->name)->isInstalled(), 'The term should be installed.');

        //3. Add some data
        $this->getTaxonomy()->addTerm($term->name, $object_id, ['tag1', 'tag2']);
        $term = $this->getTaxonomy()->getTerm($term->name, true);
        //check count on term
        $this->tester->assertEquals(2, $term->total_count, "Tag term count not correct ({$term->total_count})!");

        $data = $this->getTaxonomy()->getTerms($term->name, $object_id); // tag1 + tag2
        $this->tester->assertEquals(2, count($data), 'Tag term count not correct!');
        $this->tester->assertContains('tag1', $data, 'Tag1 missing in data');
        $this->tester->assertContains('tag2', $data, 'Tag1 missing in data');

        $this->getTaxonomy()->removeTerm($term->name, $object_id, ['name' => 'tag1']);
        $data = $this->getTaxonomy()->getTerms($term->name, $object_id); // tag1 + tag2
        $this->tester->assertEquals(1, count($data), 'Tag term count not correct!');
        $this->tester->assertNotContains('tag1', $data, 'Tag1 present in data');
        $this->tester->assertContains('tag2', $data, 'Tag1 missing in data');

        $this->getTaxonomy()->removeTerm($term->name, $object_id);
        $data = $this->getTaxonomy()->getTerms($term->name, $object_id); // tag1 + tag2
        $this->tester->assertEmpty($data, 'Tag term data not correct!');
        $this->tester->assertNotContains('tag1', $data, 'Tag1 present in data');

        // 4. setTerms() test
        $term->addTerm($object_id, ['tag1', 'tag2']); // Add new terms
        $term->setTerms($object_id, ['tag1', 'tag3', 'tag4']); // Overwrite all terms of this object
        $data = $term->getTerms($object_id);

        $this->tester->assertEquals(3, count($data), 'Wrong term count!');
        $this->tester->assertContains('tag1', $data, 'Tag1 missing in data');
        $this->tester->assertContains('tag3', $data, 'Tag3 missing in data');
        $this->tester->assertContains('tag4', $data, 'Tag4 missing in data');
        $this->tester->assertNotContains('tag2', $data, 'Tag2 must not be present in data');

        // 5. getTerms test
        $data = $term->getTerms($object_id); // Get all terms of the given object
        $this->tester->assertEquals(3, count($data), 'Wrong term count!');
        
        $data = $term->getTerms(); // Get all terms currently present in the system
        $this->tester->assertEquals(4, count($data), 'Wrong term count!');
    }

    /**
     * @depends testInstall
     */
    public function testProperties()
    {
        $object_id = 3;
        //1. Add TAG taxonomy
        $term = new TaxonomyDef();
        $term->name = 'test_property';
        $term->class = PropertyTerm::className();
        $term->data_table = 'sample_property';
        $term->ref_table = SampleTable::className();
        $this->tester->assertTrue($term->save());

        //2. Create data table
        $this->tester->assertFalse($this->getTaxonomy()->getTerm($term->name)->isInstalled(), 'The term should NOT be installed.');
        $this->getTaxonomy()->getTerm($term->name)->install();
        $this->tester->assertTrue($this->getTaxonomy()->getTerm($term->name)->isInstalled(), 'The term should be installed.');

        //3. Add some data
        $this->getTaxonomy()->addTerm($term->name, $object_id, ['prop1' => 'value1', 'prop2' => 'value2']);
        $term->refresh();
        //check count on term
        $this->tester->assertEquals(2, $term->total_count, 'Tag term count not correct!');

        //check update of field
        $this->getTaxonomy()->addTerm($term->name, $object_id, ['prop1' => 'value1_update']);
        $data = $this->getTaxonomy()->getTerms($term->name, $object_id);
        $this->tester->assertEquals('value1_update', $data['prop1'], 'Property value not updated');

        // 4. Test PropertyTerm::setTerms()
        $term = $this->getTaxonomy()->getTerm($term->name);
        $term->setTerms($object_id, ['prop1' => 'value1', 'prop3' => 'value3']);
        $data = $term->getTerms($object_id);
        
        $this->tester->assertTrue(array_key_exists('prop1', $data), 'Property prop1 is missing');
        $this->tester->assertTrue(array_key_exists('prop3', $data), 'Property prop3 is missing');
        $this->tester->assertFalse(array_key_exists('prop2', $data), 'Property prop2 must not be here');

        // 5. Test PropertyTerm::removeTerm()
        $term->removeTerm($object_id, array_keys($data));
        $data = $term->getTerms($object_id);
        $this->tester->assertEmpty($data, 'Data must be empty');
    }

    public function testCategories()
    {
        // 1. Add category taxonomy
        $taxonomy = new TaxonomyDef();
        $taxonomy->name = 'test_categories';
        $taxonomy->class = CategoryTerm::className();
        $taxonomy->data_table = 'sample_categories';
        $taxonomy->ref_table = SampleTable::className();

        // 2. Create data table
        $categoryTerm = Yii::createObject($taxonomy->attributes);
        $migration = $categoryTerm->install();
        $this->runMigration($migration);
        $categoryTerm = $this->getTaxonomy()->getTerm($taxonomy->name);
        $this->tester->assertTrue($categoryTerm->isInstalled(), 'The taxonomy must be installed.');

        // ***************CategoryTerm::createCategory() tests start*****************

        // 3. Add a root category of type array
        $rootTermName = 'root';
        $this->assertExceptionThrown(function() use ($categoryTerm, $rootTermName) {
            $categoryTerm->createCategory([$rootTermName]);
        });

        // 3a. Create a root term
        $rootTerm = $categoryTerm->createCategory($rootTermName);
        $this->tester->assertNotNull($rootTerm);
        $this->tester->assertEquals($rootTermName, $rootTerm->term);

        // 4. Add a child of wrong type
        $this->assertExceptionThrown(function() use ($categoryTerm, $rootTerm) {
            $categoryTerm->createCategory((int) $rootTerm->id, 321);
        });

        // 5. Adding an array of children where the child name is not a string
        $this->assertExceptionThrown(function() use ($categoryTerm, $rootTerm) {
            $categoryTerm->createCategory((int) $rootTerm->id, [321]);
        });

        // 6. Attatching children to a non-existing parent
        $this->assertExceptionThrown(function() use ($categoryTerm) {
            $categoryTerm->createCategory(500, ['Test', 'Test2']);
        });

        // 7. Adding a child to a parent
        $childName1 = 'child1';
        $result = $categoryTerm->createCategory((int) $rootTerm->id, [$childName1]);

        $this->tester->assertEquals(1, count($result));
        $this->tester->assertEquals($childName1, $result[0]->term);
        $this->tester->assertTrue($categoryTerm->hasParent($result[0]->id));
        $this->tester->assertTrue($categoryTerm->hasChildren($rootTerm->id));
        
        $categoryTerm = $this->getTaxonomy()->getTerm($taxonomy->name);
        $terms = $categoryTerm->getTerms();
        $this->tester->assertEquals(2, count($terms));
        $this->tester->assertContains($rootTermName, $terms);
        $this->tester->assertContains($childName1, $terms);

        $childTerm1 = $result[0];

        // ***************CategoryTerm::createCategory() tests end*****************

        // ***************CategoryTerm::addTerm() tests start*****************

        // 1. Assigning one existing term to an object
        $terms = $categoryTerm->addTerm(1, $rootTerm->id);
        $this->tester->assertEquals(1, count($terms));
        $this->tester->assertEquals($rootTermName, $terms[0]->term);

        $categoryTerm = $this->getTaxonomy()->getTerm($taxonomy->name, true);
        $rootTerm = $categoryTerm->getTaxonomyTerm($rootTermName);
        $terms = $categoryTerm->getTerms(1);

        $this->tester->assertEquals(1, $categoryTerm->total_count);
        $this->tester->assertEquals(1, $rootTerm->total_count);
        $this->tester->assertEquals(1, count($terms));
        $this->tester->assertContains($rootTermName, $terms);

        // 2. Assigning one existing term to an object as an array
        $terms = $categoryTerm->addTerm(1, [$childTerm1->id]);
        $this->tester->assertEquals(1, count($terms));
        $this->tester->assertEquals($childName1, $terms[0]->term);

        $categoryTerm = $this->getTaxonomy()->getTerm($taxonomy->name, true);
        $childTerm = $categoryTerm->getTaxonomyTerm($childName1);
        $terms = $categoryTerm->getTerms(1);

        $this->tester->assertEquals(2, $categoryTerm->total_count);
        $this->tester->assertEquals(1, $childTerm->total_count);
        $this->tester->assertEquals(2, count($terms));
        $this->tester->assertContains($rootTermName, $terms);
        $this->tester->assertContains($childName1, $terms);

        // ***************CategoryTerm::addTerm() tests end*****************

        // ***************CategoryTerm::setTerms() tests start*****************

        $childName2 = 'child2';
        $childName3 = 'child3';
        $childName4 = 'child4';
        $createdTerms = $categoryTerm->createCategory((int) $rootTerm->id, [$childName2, $childName3, $childName4]);

        $terms = $categoryTerm->getTerms(1);
        $this->tester->assertEquals(2, count($terms));

        // 1. Change the terms where the object is assigned
        $result = $categoryTerm->setTerms(1, [$rootTerm->id => [$createdTerms[0]->id, $createdTerms[1]->id]]);
        $terms = $categoryTerm->getTerms(1);
        
        $this->tester->assertEquals(2, count($result));
        $this->tester->assertEquals(3, count($terms));
        $this->tester->assertContains($childName2, $terms);
        $this->tester->assertContains($childName3, $terms);

        // ***************CategoryTerm::setTerms() tests end*****************

        // ***************getChildren(), hasChildren(), getParent(), hasParent() tests start*****************

        $this->tester->assertTrue($categoryTerm->hasChildren($rootTerm->id));
        $children = $categoryTerm->getChildren($rootTerm->id);
        $this->tester->assertEquals(4, count($children));

        $this->tester->assertTrue($categoryTerm->hasParent($childTerm->id));
        $parent = $categoryTerm->getParent($childTerm->id);
        $this->tester->assertNotNull($parent);
        $this->tester->assertEquals($rootTermName, $parent->term);

        // ***************getChildren(), hasChildren(), getParent(), hasParent() tests end*****************

    }

    private function assertExceptionThrown($callback)
    {
        $exceptionTrown = false;
        try {
            $callback();
        } catch (Exception $ex) {
            $exceptionTrown = true;
        }
        $this->tester->assertTrue($exceptionTrown);
    }
}
