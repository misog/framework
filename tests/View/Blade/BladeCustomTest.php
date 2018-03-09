<?php

namespace Illuminate\Tests\View\Blade;

class BladeCustomTest extends AbstractBladeTestCase
{
    public function testCustomPhpCodeIsCorrectlyHandled()
    {
        $this->assertEquals('<?php if($test): ?> <?php @show(\'test\'); ?> <?php endif; ?>', $this->compiler->compileString("@if(\$test) <?php @show('test'); ?> @endif"));
    }

    public function testMixingYieldAndEcho()
    {
        $this->assertEquals('<?php echo $__env->yieldContent(\'title\'); ?> - <?php echo e(Config::get(\'site.title\')); ?>', $this->compiler->compileString("@yield('title') - {{Config::get('site.title')}}"));
    }

    public function testCustomExtensionsAreCompiled()
    {
        $this->compiler->extend(function ($value) {
            return str_replace('foo', 'bar', $value);
        });
        $this->assertEquals('bar', $this->compiler->compileString('foo'));
    }

    public function testCustomStatements()
    {
        $this->assertCount(0, $this->compiler->getCustomDirectives());
        $this->compiler->directive('customControl', function ($expression) {
            return "<?php echo custom_control({$expression}); ?>";
        });
        $this->assertCount(1, $this->compiler->getCustomDirectives());

        $string = '@if($foo)
@customControl(10, $foo, \'bar\')
@endif';
        $expected = '<?php if($foo): ?>
<?php echo custom_control(10, $foo, \'bar\'); ?>
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testCustomShortStatements()
    {
        $this->compiler->directive('customControl', function ($expression) {
            return '<?php echo custom_control(); ?>';
        });

        $string = '@customControl';
        $expected = '<?php echo custom_control(); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testCustomExtensionOverwritesCore()
    {
        $this->compiler->directive('foreach', function ($expression) {
            return '<?php custom(); ?>';
        });

        $string = '@foreach';
        $expected = '<?php custom(); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testCustomConditions()
    {
        $this->compiler->if('custom', function ($user) {
            return true;
        });

        $string = '@custom($user)
@endcustom';
        $expected = '<?php if (\Illuminate\Support\Facades\Blade::check(\'custom\', $user)): ?>
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testCustomIfElseConditions()
    {
        $this->compiler->if('custom', function ($anything) {
            return true;
        });

        $string = '@custom($user)
@elsecustom($product)
@else
@endcustom';
        $expected = '<?php if (\Illuminate\Support\Facades\Blade::check(\'custom\', $user)): ?>
<?php elseif (\Illuminate\Support\Facades\Blade::check(\'custom\', $product)): ?>
<?php else: ?>
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testCustomComponents()
    {
        $this->compiler->component('app.components.alert', 'alert');

        $string = '@alert
@endalert';
        $expected = '<?php $__env->startComponent(\'app.components.alert\'); ?>
<?php echo $__env->renderComponent(); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testCustomComponentsWithSlots()
    {
        $this->compiler->component('app.components.alert', 'alert');

        $string = '@alert([\'type\' => \'danger\'])
@endalert';
        $expected = '<?php $__env->startComponent(\'app.components.alert\', [\'type\' => \'danger\']); ?>
<?php echo $__env->renderComponent(); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testCustomComponentsDefaultAlias()
    {
        $this->compiler->component('app.components.alert');

        $string = '@alert
@endalert';
        $expected = '<?php $__env->startComponent(\'app.components.alert\'); ?>
<?php echo $__env->renderComponent(); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testCustomComponentsWithExistingDirective()
    {
        $this->compiler->component('app.components.foreach');

        $string = '@foreach
@endforeach';
        $expected = '<?php $__env->startComponent(\'app.components.foreach\'); ?>
<?php echo $__env->renderComponent(); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testCustomIncludes()
    {
        $this->compiler->include('app.includes.input', 'input');

        $string = '@input';
        $expected = '<?php echo $__env->make(\'app.includes.input\', [], array_except(get_defined_vars(), array(\'__data\', \'__path\')))->render(); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testCustomIncludesWithData()
    {
        $this->compiler->include('app.includes.input', 'input');

        $string = '@input([\'type\' => \'email\'])';
        $expected = '<?php echo $__env->make(\'app.includes.input\', [\'type\' => \'email\'], array_except(get_defined_vars(), array(\'__data\', \'__path\')))->render(); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testCustomIncludesDefaultAlias()
    {
        $this->compiler->include('app.includes.input');

        $string = '@input';
        $expected = '<?php echo $__env->make(\'app.includes.input\', [], array_except(get_defined_vars(), array(\'__data\', \'__path\')))->render(); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testCustomIncludesWithExistingDirective()
    {
        $this->compiler->include('app.includes.foreach');

        $string = '@foreach';
        $expected = '<?php echo $__env->make(\'app.includes.foreach\', [], array_except(get_defined_vars(), array(\'__data\', \'__path\')))->render(); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }
}
