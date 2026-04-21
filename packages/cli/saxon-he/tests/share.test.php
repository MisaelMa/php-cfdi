<?php

use Cli\SaxonHe\Transform;

test('share builds catalog option', function () {
    $t = new Transform();
    $t->catalog('catalog.xml');
    expect($t->commandline)->toContain('-catalog:catalog.xml');
});

test('share builds dtd option', function () {
    $t = new Transform();
    $t->dtd('on');
    expect($t->commandline)->toContain('-dtd:on');
});

test('share builds expand option', function () {
    $t = new Transform();
    $t->expand('off');
    expect($t->commandline)->toContain('-expand:off');
});

test('share builds ext option', function () {
    $t = new Transform();
    $t->ext('on');
    expect($t->commandline)->toContain('-ext:on');
});

test('share builds init option', function () {
    $t = new Transform();
    $t->init('MyInit');
    expect($t->commandline)->toContain('-init:MyInit');
});

test('share builds l option', function () {
    $t = new Transform();
    $t->l('on');
    expect($t->commandline)->toContain('-l:on');
});

test('share builds now option', function () {
    $t = new Transform();
    $t->now('2024-01-01');
    expect($t->commandline)->toContain('-now:2024-01-01');
});

test('share builds o option', function () {
    $t = new Transform();
    $t->o('output.xml');
    expect($t->commandline)->toContain('-o:output.xml');
});

test('share builds opt option', function () {
    $t = new Transform();
    $t->opt('c');
    expect($t->commandline)->toContain('-opt:-c');
});

test('share builds outval option', function () {
    $t = new Transform();
    $t->outval('recover');
    expect($t->commandline)->toContain('-outval:recover');
});

test('share builds p option', function () {
    $t = new Transform();
    $t->p('on');
    expect($t->commandline)->toContain('-p:on');
});

test('share builds quit option', function () {
    $t = new Transform();
    $t->quit('off');
    expect($t->commandline)->toContain('-quit:off');
});

test('share builds r option', function () {
    $t = new Transform();
    $t->r('MyResolver');
    expect($t->commandline)->toContain('-r:MyResolver');
});

test('share builds repeat option', function () {
    $t = new Transform();
    $t->repeat(5);
    expect($t->commandline)->toContain('-repeat:5');
});

test('share s throws on missing file', function () {
    $t = new Transform();
    $t->s('/nonexistent/file.xml');
})->throws(\RuntimeException::class);

test('share builds sa option', function () {
    $t = new Transform();
    $t->sa();
    expect($t->commandline)->toContain('-sa');
});

test('share builds scmin option', function () {
    $t = new Transform();
    $t->scmin('schema.xsd');
    expect($t->commandline)->toContain('-scmin:schema.xsd');
});

test('share builds strip option', function () {
    $t = new Transform();
    $t->strip('all');
    expect($t->commandline)->toContain('-relocate:all');
});

test('share builds t option', function () {
    $t = new Transform();
    $t->t();
    expect($t->commandline)->toContain('-t');
});

test('share builds uppercase T trace option', function () {
    $t = new Transform();
    $t->_T_('MyTraceListener');
    expect($t->commandline)->toContain('-T:MyTraceListener');
});

test('share builds TB option', function () {
    $t = new Transform();
    $t->TB('trace.xml');
    expect($t->commandline)->toContain('-TB:trace.xml');
});

test('share builds TJ option', function () {
    $t = new Transform();
    $t->TJ();
    expect($t->commandline)->toContain('-TJ');
});

test('share builds Tlevel option', function () {
    $t = new Transform();
    $t->Tlevel('high');
    expect($t->commandline)->toContain('-Tlevel:high');
});

test('share builds Tout option', function () {
    $t = new Transform();
    $t->Tout('trace-output.xml');
    expect($t->commandline)->toContain('-Tout:trace-output.xml');
});

test('share builds TP option', function () {
    $t = new Transform();
    $t->TP('profile.html');
    expect($t->commandline)->toContain('-TP:profile.html');
});

test('share builds traceout option', function () {
    $t = new Transform();
    $t->traceout('trace.txt');
    expect($t->commandline)->toContain('-traceout:trace.txt');
});

test('share builds tree option', function () {
    $t = new Transform();
    $t->tree('tiny');
    expect($t->commandline)->toContain('-tree:tiny');
});

test('share builds u option', function () {
    $t = new Transform();
    $t->u();
    expect($t->commandline)->toContain('-u');
});

test('share builds val option', function () {
    $t = new Transform();
    $t->val('strict');
    expect($t->commandline)->toContain('-val:strict');
});

test('share builds x option', function () {
    $t = new Transform();
    $t->x('MyParser');
    expect($t->commandline)->toContain('-x:MyParser');
});

test('share builds xi option', function () {
    $t = new Transform();
    $t->xi('on');
    expect($t->commandline)->toContain('-xi:on');
});

test('share builds xmlversion option', function () {
    $t = new Transform();
    $t->xmlversion('1.1');
    expect($t->commandline)->toContain('-xmlversion:1.1');
});

test('share builds xsd option', function () {
    $t = new Transform();
    $t->xsd('schema.xsd');
    expect($t->commandline)->toContain('-xsd:schema.xsd');
});

test('share builds xsdversion option', function () {
    $t = new Transform();
    $t->xsdversion('1.1');
    expect($t->commandline)->toContain('-xsdversion:1.1');
});

test('share builds xsiloc option', function () {
    $t = new Transform();
    $t->xsiloc('off');
    expect($t->commandline)->toContain('-xsiloc:off');
});

test('share builds feature option', function () {
    $t = new Transform();
    $t->feature('allow-external-functions=true');
    expect($t->commandline)->toContain('--feature:allow-external-functions=true');
});
