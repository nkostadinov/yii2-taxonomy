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
        
        $this->tester->assertArrayHasKey('prop1', $data, 'Property prop1 is missing');
        $this->tester->assertArrayHasKey('prop3', $data, 'Property prop3 is missing');
        $this->tester->assertArrayNotHasKey('prop2', $data, 'Property prop2 must not be here');

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

        // 3. Add a root category without an object id
        $rootTermName = 'root';
        $categoryTerm->addTerm(null, [$rootTermName]);
        $categoryTerm = $this->getTaxonomy()->getTerm($taxonomy->name, true);
        $rootTerm = $categoryTerm->getTaxonomyTerm($rootTermName);
        $terms = $categoryTerm->getTerms(null);
        // Check whether everything is properly inserted
        $this->tester->assertEquals(0, $categoryTerm->total_count);
        $this->tester->assertEquals(0, $rootTerm->total_count);
        $this->tester->assertEquals(1, count($terms));
        $this->tester->assertEquals($rootTermName, $terms[0]);
        // Check for parents
        $this->tester->assertNull($categoryTerm->getParent($terms[0]));
        $this->tester->assertFalse($categoryTerm->hasParent($terms[0]));
        // Check for children
        $this->tester->assertEmpty($categoryTerm->getChildren($terms[0]));
        $this->tester->assertFalse($categoryTerm->hasChildren($terms[0]));

        // 4. Add child to the root
        $childTermName1 = 'child1';
        $categoryTerm->addTerm(null, [$rootTermName => $childTermName1]);
        $categoryTerm = $this->getTaxonomy()->getTerm($taxonomy->name, true);
        $childTerm1 = $categoryTerm->getTaxonomyTerm($childTermName1);
        $terms = $categoryTerm->getTerms(null);
        // Check whether everything is properly inserted
        $this->tester->assertEquals(0, $categoryTerm->total_count);
        $this->tester->assertEquals(0, $childTerm1->total_count);
        $this->tester->assertEquals(2, count($terms));
        $this->tester->assertContains($childTermName1, $terms);
        // Check for parents
        $this->tester->assertTrue($categoryTerm->hasParent($childTermName1));
        $this->tester->assertEquals($rootTermName, $categoryTerm->getParent($childTermName1));
        // Check for children
        $this->tester->assertEmpty($categoryTerm->getChildren($childTermName1));
        $this->tester->assertFalse($categoryTerm->hasChildren($childTermName1));
        // Check the children of the root
        $rootChildren = $categoryTerm->getChildren($rootTermName);
        $this->tester->assertTrue($categoryTerm->hasChildren($rootTermName));
        $this->tester->assertEquals(1, count($rootChildren));
        $this->tester->assertContains($childTermName1, $rootChildren);

        // 5. Test adding more than one child at a time
        $childTermName2 = 'child2';
        $childTermName3 = 'child3';
        $categoryTerm->addTerm(null, [$rootTermName => [$childTermName2, $childTermName3]]);
        $categoryTerm = $this->getTaxonomy()->getTerm($taxonomy->name, true);
        $terms = $categoryTerm->getTerms(null);

        // Test whether all child terms are attached to the root
        $this->tester->assertEquals(4, count($terms));
        $this->tester->assertEquals(3, count($categoryTerm->getChildren($rootTermName)));

        // 6. Test adding term to an existing object
        $rootTermName2 = 'root2';
        $categoryTerm->addTerm(1, $rootTermName2); // Add a term as a string, not as an array
        $categoryTerm = $this->getTaxonomy()->getTerm($taxonomy->name, true);
        $rootTerm2 = $categoryTerm->getTaxonomyTerm($rootTermName2);

        // Check whether everything is properly inserted
        $terms = $categoryTerm->getTerms(1);
        $this->tester->assertEquals(1, count($terms));
        $this->tester->assertContains($rootTermName2, $terms);
        // Check the counters
        $this->tester->assertEquals(1, $categoryTerm->total_count);
        $this->tester->assertEquals(1, $rootTerm2->total_count);

        // Check whether all terms will be returned
        $terms = $categoryTerm->getTerms(null);
        $this->tester->assertEquals(5, count($terms));

        // Add child

        $childTermName4 = 'child4';
        $categoryTerm->addTerm(1, [$rootTermName2 => $childTermName4]);
        $categoryTerm = $this->getTaxonomy()->getTerm($taxonomy->name, true);
        $rootTerm2 = $categoryTerm->getTaxonomyTerm($rootTermName2);
        $childTerm4 = $categoryTerm->getTaxonomyTerm($childTermName4);

        $terms = $categoryTerm->getTerms(1);
        $this->tester->assertEquals(2, count($terms));
        $this->tester->assertEquals(2, $categoryTerm->total_count);
        $this->tester->assertEquals(1, $rootTerm2->total_count);
        $this->tester->assertEquals(1, $childTerm4->total_count);

        // 7. Loop detection test. Add the root as a child of one of the children
        $exceptionTrown = false;
        try {
            $categoryTerm->addTerm(null, [$childTermName3 => $rootTermName]);
        } catch (Exception $ex) {
            $exceptionTrown = true;
        }
        $this->tester->assertTrue($exceptionTrown);

        // 8. Adding two hierarchies at once
        TaxonomyTerms::deleteAll();

        $categoryTerm->addTerm(null, [
            $rootTermName => [
                $childTermName1,
                $childTermName2
            ],
            $rootTermName2
        ]);
        $categoryTerm = $this->getTaxonomy()->getTerm($taxonomy->name, true);
        $terms = $categoryTerm->getTerms(null);

        $this->tester->assertEquals(4, count($terms));

        // 9. setTerms() test
        TaxonomyTerms::deleteAll();
        
        $categoryTerm->addTerm(1, [$rootTermName, $rootTermName2 => [$childTermName1, $childTermName2]]);
        $terms = $categoryTerm->getTerms(1);
        $this->tester->assertEquals(4, count($terms));

        $categoryTerm->setTerms(1, [$rootTermName, $rootTermName2 => [$childTermName1, 'child2Changed']]);
        $terms = $categoryTerm->getTerms(1);
        $this->tester->assertEquals(4, count($terms));
        $this->tester->assertContains($rootTermName, $terms);
        $this->tester->assertContains($rootTermName2, $terms);
        $this->tester->assertContains($childTermName1, $terms);
        $this->tester->assertContains('child2Changed', $terms);
        
        $categoryTerm->setTerms(1, [$rootTermName, $rootTermName2 => [$childTermName1]]);
        $terms = $categoryTerm->getTerms(1);
        $this->tester->assertEquals(3, count($terms));
        $this->tester->assertContains($rootTermName, $terms);
        $this->tester->assertContains($rootTermName2, $terms);
        $this->tester->assertContains($childTermName1, $terms);
        
        $categoryTerm->setTerms(1, [$rootTermName, 'root2Changed' => [$childTermName1]]);
        $terms = $categoryTerm->getTerms(1);
        $this->tester->assertEquals(3, count($terms));
        $this->tester->assertContains($rootTermName, $terms);
        $this->tester->assertContains('root2Changed', $terms);
        $this->tester->assertContains($childTermName1, $terms);

        $categoryTerm->setTerms(1, [$rootTermName]);
        $terms = $categoryTerm->getTerms(1);
        $this->tester->assertEquals(1, count($terms));
        $this->tester->assertContains($rootTermName, $terms);

        $categoryTerm->setTerms(1, ['rootChanged']);
        $terms = $categoryTerm->getTerms(1);
        $this->tester->assertEquals(1, count($terms));
        $this->tester->assertContains('rootChanged', $terms);

        $categoryTerm->setTerms(1);
        $terms = $categoryTerm->getTerms(1);
        $this->tester->assertEquals(0, count($terms));
    }
}
