<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\PageBundle\Tests\Functional\Controller;

use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class AdminControllerTest extends SuluTestCase
{
    public function testRouteConfig()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/admin/config');

        $this->assertHttpStatusCode(200, $client->getResponse());
        $response = json_decode($client->getResponse()->getContent());

        $routeConfig = $response->sulu_admin->routes;

        $formRoute = null;
        foreach ($routeConfig as $route) {
            if ('sulu_page.page_add_form.details' === $route->name) {
                $formRoute = $route;
                break;
            }
        }

        $this->assertEquals('Edit', $formRoute->options->toolbarActions[3]->options->label);
    }

    public function testTeaserConfig()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/admin/config');

        $this->assertHttpStatusCode(200, $client->getResponse());
        $response = json_decode($client->getResponse()->getContent());

        $pageConfig = $response->sulu_page;

        $this->assertCount(1, (array) $pageConfig->teaser);
        $this->assertEquals('Page', $pageConfig->teaser->pages->title);
        $this->assertEquals('pages', $pageConfig->teaser->pages->resourceKey);
    }

    public function testWebspacesConfig()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/admin/config');

        $this->assertHttpStatusCode(200, $client->getResponse());
        $response = json_decode($client->getResponse()->getContent());

        $pageConfig = $response->sulu_page;

        $this->assertCount(3, (array) $pageConfig->webspaces);
        $this->assertEquals('destination_io', $pageConfig->webspaces->destination_io->key);
        $this->assertEquals('sulu_io', $pageConfig->webspaces->sulu_io->key);
        $this->assertEquals('test_io', $pageConfig->webspaces->test_io->key);

        $this->assertEquals('Destination CMF', $pageConfig->webspaces->destination_io->name);
        $this->assertEquals('default', $pageConfig->webspaces->destination_io->defaultTemplates->page);
        $this->assertEquals('overview', $pageConfig->webspaces->destination_io->defaultTemplates->homepage);
        $this->assertEquals('main', $pageConfig->webspaces->destination_io->navigations[0]->key);
        $this->assertEquals('footer', $pageConfig->webspaces->destination_io->navigations[1]->key);
        $this->assertEquals('leaf', $pageConfig->webspaces->destination_io->resourceLocatorStrategy->inputType);
        $this->assertEquals([], $pageConfig->webspaces->destination_io->customUrls);
        $this->assertEquals([], $pageConfig->webspaces->destination_io->_permissions);
    }

    public function testPagesListMetadataAction()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/admin/metadata/list/pages');

        $this->assertHttpStatusCode(200, $client->getResponse());
        $response = json_decode($client->getResponse()->getContent());

        $this->assertObjectHasAttribute('id', $response);
        $this->assertObjectHasAttribute('title', $response);
        $this->assertObjectHasAttribute('published', $response);

        $this->assertEquals('ID', $response->id->label);
        $this->assertEquals('string', $response->id->type);
    }

    public function testPagesFormMetadataAction()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/admin/metadata/form/page');

        $this->assertHttpStatusCode(200, $client->getResponse());
        $response = json_decode($client->getResponse()->getContent());

        $types = $response->types;

        $this->assertObjectHasAttribute('default', $types);
        $this->assertObjectHasAttribute('overview', $types);
        $this->assertObjectHasAttribute('blocks', $types);

        $defaultType = $types->default;
        $this->assertObjectHasAttribute('name', $defaultType);
        $this->assertEquals('default', $defaultType->name);
        $this->assertObjectHasAttribute('title', $defaultType);
        $this->assertEquals('Standard page', $defaultType->title);
        $this->assertObjectHasAttribute('form', $defaultType);
        $this->assertObjectHasAttribute('title', $defaultType->form);
        $this->assertObjectHasAttribute('url', $defaultType->form);
        $this->assertEquals('sulu.rlp.part', $defaultType->form->title->tags[0]->name);
        $this->assertEquals(1, $defaultType->form->title->tags[0]->priority);
        $this->assertObjectHasAttribute('schema', $defaultType);
        $this->assertEquals(['title'], $defaultType->schema->required);

        $overviewType = $types->overview;
        $this->assertObjectHasAttribute('name', $overviewType);
        $this->assertEquals('overview', $overviewType->name);
        $this->assertObjectHasAttribute('title', $overviewType);
        $this->assertEquals('Overview', $overviewType->title);
        $this->assertObjectHasAttribute('form', $overviewType);
        $this->assertObjectHasAttribute('title', $overviewType->form);
        $this->assertObjectHasAttribute('tags', $overviewType->form);
        $this->assertObjectHasAttribute('url', $overviewType->form);
        $this->assertObjectHasAttribute('article', $overviewType->form);
        $this->assertObjectHasAttribute('schema', $overviewType);
        $this->assertEquals([], $overviewType->schema->required);
        $this->assertCount(1, (array) $overviewType->schema->properties);
        $this->assertEquals('array', $overviewType->schema->properties->block->type);
        $this->assertEquals([], $overviewType->schema->properties->block->items->required);
        $this->assertCount(2, $overviewType->schema->properties->block->items->anyOf);
        $this->assertEquals(['title', 'type'], $overviewType->schema->properties->block->items->anyOf[0]->required);
        $this->assertCount(1, (array) $overviewType->schema->properties->block->items->anyOf[0]->properties);
        $this->assertEquals('type', $overviewType->schema->properties->block->items->anyOf[0]->properties->type->name);
        $this->assertEquals('type1', $overviewType->schema->properties->block->items->anyOf[0]->properties->type->const);
        $this->assertEquals(['image', 'type'], $overviewType->schema->properties->block->items->anyOf[1]->required);
        $this->assertCount(1, (array) $overviewType->schema->properties->block->items->anyOf[1]->properties);
        $this->assertEquals('type', $overviewType->schema->properties->block->items->anyOf[1]->properties->type->name);
        $this->assertEquals('type2', $overviewType->schema->properties->block->items->anyOf[1]->properties->type->const);

        $blocksType = $types->blocks;
        $this->assertEquals(1, $blocksType->form->block->minOccurs);
        $this->assertEquals(5, $blocksType->form->block->maxOccurs);
        $this->assertEquals(2, $blocksType->form->block->types->article->form->lines->minOccurs);
        $this->assertEquals(2, $blocksType->form->block->types->article->form->lines->maxOccurs);

        $smartContentType = $types->smartcontent;
        $smartContentOptions = $smartContentType->form->smart_content->options;
        $this->assertEquals('properties', $smartContentOptions->properties->name);
        $this->assertEquals('collection', $smartContentOptions->properties->type);
        $this->assertCount(5, $smartContentOptions->properties->value);
        $this->assertEquals('article', $smartContentOptions->properties->value[0]->name);
        $this->assertEquals('article', $smartContentOptions->properties->value[0]->value);
        $this->assertEquals('excerptTitle', $smartContentOptions->properties->value[1]->name);
        $this->assertEquals('excerpt.title', $smartContentOptions->properties->value[1]->value);
        $this->assertEquals('excerptTags', $smartContentOptions->properties->value[2]->name);
        $this->assertEquals('excerpt.tags', $smartContentOptions->properties->value[2]->value);
        $this->assertEquals('excerptImages', $smartContentOptions->properties->value[3]->name);
        $this->assertEquals('excerpt.images', $smartContentOptions->properties->value[3]->value);
        $this->assertEquals('excerptDescription', $smartContentOptions->properties->value[4]->name);
        $this->assertEquals('excerpt.description', $smartContentOptions->properties->value[4]->value);
    }

    public function testPageSeoFormMetadataAction()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/admin/metadata/form/page_seo');

        $this->assertHttpStatusCode(200, $client->getResponse());
        $response = json_decode($client->getResponse()->getContent());

        $form = $response->form;

        $this->assertObjectHasAttribute('search_result', $form);
        $this->assertObjectHasAttribute('ext/seo/title', $form);
        $this->assertObjectHasAttribute('ext/seo/description', $form);

        $schema = $response->schema;

        $this->assertEquals(['required' => []], (array) $schema);
    }

    public function testPageExcerptFormMetadataAction()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/admin/metadata/form/page_excerpt');

        $this->assertHttpStatusCode(200, $client->getResponse());
        $response = json_decode($client->getResponse()->getContent());

        $form = $response->form;

        $this->assertObjectHasAttribute('ext/excerpt/title', $form);
        $this->assertObjectHasAttribute('ext/excerpt/more', $form);
        $this->assertObjectHasAttribute('ext/excerpt/description', $form);

        $schema = $response->schema;

        $this->assertEquals(['required' => []], (array) $schema);
    }

    public function testPageSettingFormMetadataAction()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/admin/metadata/form/page_settings');

        $this->assertHttpStatusCode(200, $client->getResponse());
        $response = json_decode($client->getResponse()->getContent());

        $form = $response->form;

        $this->assertObjectHasAttribute('navContexts', $form);
        $this->assertObjectHasAttribute('pageType', $form);
        $this->assertObjectHasAttribute('shadowPage', $form);

        $schema = $response->schema;

        $this->assertCount(2, $schema->allOf);
        $this->assertEquals(['nodeType'], $schema->allOf[0]->required);
    }
}
