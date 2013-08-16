<?php
/**
 * This file is part of the Everon framework.
 *
 * (c) Oliwier Ptak <oliwierptak@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\Test;

class ViewTest extends \Everon\TestCase
{

    public function testConstructor()
    {
        $View = new \Everon\Test\MyView($this->Environment->getViewTemplate(), function(){});
        $this->assertInstanceOf('\Everon\View', $View);
    }

    public function setupTemplateFile($filename, $content)
    {
        file_put_contents($filename, $content);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testSetGet(\Everon\Interfaces\View $View)
    {
        $View->set('test', 'me');
        $this->assertEquals('me', $View->get('test'));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testSetOutputShouldCopyAssignedDataFromViewToOutputTemplate(\Everon\Interfaces\View $View)
    {
        $Form = new \Everon\View\Element\Form([
            'action' => '/login/submit'
        ]);

        $FakeUser = new \Everon\Helper\Popo(['username'=>'test']);
        $View->set('User', $FakeUser);
        $View->set('Form', $Form);

        /**
         * @var \Everon\Interfaces\TemplateContainer $Template
         */
        $Template = $View->getTemplate('ViewTest_form', ['Test'=>'I was Included']);
        $View->setOutput($Template);

        $expected =
<<<EOF
I was Included
<form action="/login/submit" method="post" enctype="multipart/form-data">
    <input type="hidden" name="token" value="3">
    <table border="0">
    <tr>
        <td>Username</td>
        <td>:</td>
        <td><input type="text" value="test" name="username"></td>
    </tr>
    <tr>
        <td>Password</td>
        <td>:</td>
        <td><input type="password" name="password" /></td>
    </tr>
    <tr>
        <td colspan="3"><input type="submit"></td>
    </tr>
    </table>
</form>
EOF;
        $actual = (string) $View->getOutput();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOutputTemplate(\Everon\Interfaces\View $View)
    {
        $Form = new \Everon\View\Element\Form([
            'action' => '/login/submit'
        ]);

        $expected =
<<<EOF
<b>Included test</b>
<h1>I was included</h1>
<form action="/login/submit" method="post" enctype="multipart/form-data">
    <input type="hidden" name="token" value="3">
    <table border="0">
    <tr>
        <td>Username</td>
        <td>:</td>
        <td><input type="text" value="test" name="username"></td>
    </tr>
    <tr>
        <td>Password</td>
        <td>:</td>
        <td><input type="password" name="password" /></td>
    </tr>
    <tr>
        <td colspan="3"><input type="submit"></td>
    </tr>
    </table>
</form>
EOF;

        $FakeUser = new \Everon\Helper\Popo(['username'=>'test']);

        /**
         * @var \Everon\Interfaces\TemplateContainer $Template
         */
        $Template = $View->getTemplate('ViewTest_form', [
            'Form' => $Form,
            'User' => $FakeUser
        ]);

        $Include = $View->getTemplate('ViewTest_include', [
            'name' => 'test',
        ]);

        $Include2 = $View->getTemplate('ViewTest_include_2', [
            'text' => 'I was included',
        ]);

        $Include->set('IncludeMeNow', $Include2);
        $Template->set('Test', $Include);

        $View->setOutput($Template);
        $Output = $View->getOutput();

        $actual = (string) $Output;

        $this->assertInstanceOf('\Everon\Interfaces\TemplateContainer', $Output);
        $this->assertEquals($expected, $actual);
        $this->assertInstanceOf('\Everon\Interfaces\TemplateContainer', $Template);
        $this->assertEquals($this->getTemplateDirectory().'ViewTest_form.htm', $Template->getTemplateFile());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testSetOutput(\Everon\Interfaces\View $View)
    {
        $View->setOutput('');
        $this->assertInstanceOf('\Everon\Interfaces\TemplateContainer', $View->getOutput());

        $View->setOutput([]);
        $this->assertInstanceOf('\Everon\Interfaces\TemplateContainer', $View->getOutput());
        
        $View->setOutput(new \Everon\View\Template\Container('', []));
        $this->assertInstanceOf('\Everon\Interfaces\TemplateContainer', $View->getOutput());
    }

    /**
     * @dataProvider dataProvider
     * @expectedException \Everon\Exception\Template
     * @expectedExceptionMessage Invalid Output type
     */
    public function testSetOutputShouldThrowExceptionWhenWrongInputIsSet(\Everon\Interfaces\View $View)
    {
        $View->setOutput(null);
        $this->assertInstanceOf('\Everon\Interfaces\TemplateContainer', $View->getOutput());
    }
    
    /**
     * @dataProvider dataProvider
     */
    public function testGetOutputShouldSetOutputToEmptyStringWhenNull(\Everon\Interfaces\View $View)
    {
        $PropertyOutput = $this->getProtectedProperty('\Everon\View', 'Output');
        $PropertyOutput->setValue($View, null);
        
        $Output = $View->getOutput();
        $this->assertEquals('', $Output);
    }

    public function dataProvider()
    {
        $Factory = $this->getFactory();

        $ViewManager = $Factory->buildViewManager(['Curly'], $this->Environment->getViewTemplate(), $this->Environment->getWebCache());
        $Method = $this->getProtectedMethod('Everon\View\Manager\Everon', 'compileTemplate');
        
        $Compiler = function(\Everon\Interfaces\TemplateContainer $Template) use ($ViewManager, $Method) {
            return $Method->invoke($ViewManager, $Template);
        };
        
        $View = $Factory->buildView('MyView', $this->Environment->getViewTemplate(), $Compiler, 'Everon\Test');
        
        return [
            [$View]
        ];
    }

}
