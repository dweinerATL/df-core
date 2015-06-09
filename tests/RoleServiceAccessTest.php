<?php
/**
 * This file is part of the DreamFactory Rave(tm)
 *
 * DreamFactory Rave(tm) <http://github.com/dreamfactorysoftware/rave>
 * Copyright 2012-2014 DreamFactory Software, Inc. <support@dreamfactory.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use DreamFactory\Rave\Enums\VerbsMask;
use DreamFactory\Rave\Utility\Session;
use DreamFactory\Rave\Enums\ServiceRequestorTypes;

class RoleServiceAccessTest extends \DreamFactory\Rave\Testing\TestCase
{
    protected $rsaKey = 'role.services';

    protected $apiKey = '1234567890';

    protected $rsa = [ ];

    public function tearDown()
    {
        \DreamFactory\Rave\Models\User::whereEmail('jdoe@dreamfactory.com')->delete();

        parent::tearDown();
    }

    public function testSysAdmin()
    {
        $user = \DreamFactory\Rave\Models\User::find(1);
        $this->be($user);
        Session::setUserInfo($user);
        $permission = Session::getServicePermissions( 'system', '*' );

        $this->assertEquals(
            $permission,
            ( VerbsMask::NONE_MASK | VerbsMask::GET_MASK | VerbsMask::POST_MASK | VerbsMask::PUT_MASK | VerbsMask::PATCH_MASK | VerbsMask::DELETE_MASK )
        );

        $nonAdminUser = \DreamFactory\Rave\Models\User::create([
            'name'              => 'John Doe',
            'first_name'        => 'John',
            'last_name'         => 'Doe',
            'email'             => 'jdoe@dreamfactory.com',
            'password'          => 'test1234',
            'security_question' => 'Make of your first car?',
            'security_answer'   => 'mazda',
            'is_active'         => 1
        ]);

        $this->be($nonAdminUser);
        Session::setUserInfo($nonAdminUser);
        $permission = Session::getServicePermissions( 'system', '*' );

        $this->assertEquals( VerbsMask::NONE_MASK, $permission );
    }

    public function testGetNullNull()
    {
        $this->setRsa( null, null, [ 'GET' ] );

        $this->assertEquals( VerbsMask::GET_MASK, $this->check( null ), 'check(null)' );
        $this->assertEquals( VerbsMask::GET_MASK, $this->check( 'user' ), 'check(user)' );
        $this->assertEquals( VerbsMask::GET_MASK, $this->check( 'system' ), 'check(system)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'system', 'app' ), 'check(system, app)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'system', 'app/1' ), 'check(system, app/1)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( null, 'admin' ), 'check(null, admin)' ); //not possible anyway!
    }

    public function testGetNullBlank()
    {
        $this->setRsa( null, '', [ 'GET' ] );

        $this->assertEquals( VerbsMask::GET_MASK, $this->check( null ), 'check(null)' );
        $this->assertEquals( VerbsMask::GET_MASK, $this->check( 'user' ), 'check(user)' );
        $this->assertEquals( VerbsMask::GET_MASK, $this->check( 'system' ), 'check(system)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'system', 'app' ), 'check(system, app)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'system', 'app/1' ), 'check(system, app/1)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( null, 'admin' ), 'check(null, admin)' ); //not possible anyway!
    }

    public function testGetNullStar()
    {
        $this->setRsa( null, '*', [ 'GET' ] );

        $this->assertEquals( VerbsMask::GET_MASK, $this->check( null ), 'check(null)' );
        $this->assertEquals( VerbsMask::GET_MASK, $this->check( 'user' ), 'check(user)' );
        $this->assertEquals( VerbsMask::GET_MASK, $this->check( 'system' ), 'check(system)' );
        $this->assertEquals( VerbsMask::GET_MASK, $this->check( 'system', 'app' ), 'check(system, app)' );
        $this->assertEquals( VerbsMask::GET_MASK, $this->check( 'system', 'app/1' ), 'check(system, app/1)' );
        $this->assertEquals( VerbsMask::GET_MASK, $this->check( 'system', 'app/foo/bar' ), 'check(system, app/foo/bar)' );
        $this->assertEquals( VerbsMask::GET_MASK, $this->check( null, 'admin' ), 'check(null, admin)' ); //not possible anyway!
    }

    public function testGetSystemNull()
    {
        $this->setRsa( 'system', null, [ 'GET' ] );

        $this->assertEquals( VerbsMask::GET_MASK, $this->check( 'system' ), 'check(system)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'user' ), 'check(user)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'system', 'app' ), 'check(system, app)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'system', 'app/1' ), 'check(system, app/1)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( null, 'admin' ), 'check(null, admin)' ); //not possible anyway!
    }

    public function testGetSystemBlank()
    {
        $this->setRsa( 'system', '', [ 'GET' ] );

        $this->assertEquals( VerbsMask::GET_MASK, $this->check( 'system' ), 'check(system)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'user' ), 'check(user)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'system', 'app' ), 'check(system, app)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'system', 'app/1' ), 'check(system, app/1)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( null, 'admin' ), 'check(null, admin)' ); //not possible anyway!
    }

    public function testGetSystemStar()
    {
        $this->setRsa( 'system', '*', [ 'GET', 'DELETE' ] );
        $this->setRsa( 'system', 'admin/password', [ 'POST' ] );

        $this->assertEquals( VerbsMask::arrayToMask( [ 'GET', 'DELETE' ] ), $this->check( 'system' ), 'check(system)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'user' ), 'check(user)' );
        $this->assertEquals( VerbsMask::arrayToMask( [ 'GET', 'DELETE' ] ), $this->check( 'system', 'app' ), 'check(system, app)' );
        $this->assertEquals( VerbsMask::arrayToMask( [ 'GET', 'DELETE' ] ), $this->check( 'system', 'app/1' ), 'check(system, app/1)' );
        $this->assertEquals( VerbsMask::arrayToMask( [ 'GET', 'DELETE' ] ), $this->check( 'system', 'role' ), 'check(system, role)' );
        $this->assertEquals( VerbsMask::arrayToMask( [ 'GET', 'DELETE' ] ), $this->check( 'system', 'role/1' ), 'check(system, role/1)' );
        $this->assertEquals( VerbsMask::POST_MASK, $this->check( 'system', 'admin/password' ), 'check(system, admin/password)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( null, 'admin' ), 'check(null, admin)' ); //not possible anyway!
    }

    public function testGetSystemApp()
    {
        $this->setRsa( 'system', 'app', [ 'GET' ] );

        $this->assertEquals( VerbsMask::GET_MASK, $this->check( 'system', 'app' ), 'check(system, app)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'system', 'app/1' ), 'check(system app/1)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'system', '' ), 'check(system, "")' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'system' ), 'check(system)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'system', 'role' ), 'check(system role)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'system' ), 'check(system)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'user' ), 'check(user)' );
    }

    public function testGetSystemAppStar()
    {
        $this->setRsa( 'system', 'app/*', [ 'GET' ] );

        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'system', 'app' ), 'check(system, app)' );
        $this->assertEquals( VerbsMask::GET_MASK, $this->check( 'system', 'app/1' ), 'check(system app/1)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'system', '' ), 'check(system, "")' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'system' ), 'check(system)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'system', 'role' ), 'check(system role)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'system' ), 'check(system)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'user' ), 'check(user)' );
    }

    public function testGetSystemAppStar2()
    {
        $this->setRsa( 'system', 'app', [ 'GET' ] );
        $this->setRsa( 'system', 'app/*', [ 'GET' ] );

        $this->assertEquals( VerbsMask::GET_MASK, $this->check( 'system', 'app' ), 'check(system, app)' );
        $this->assertEquals( VerbsMask::GET_MASK, $this->check( 'system', 'app/1' ), 'check(system app/1)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'system', '' ), 'check(system, "")' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'system' ), 'check(system)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'system', 'role' ), 'check(system role)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'system' ), 'check(system)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'user' ), 'check(user)' );
    }

    public function testMostSpecificOverwrite()
    {
        $this->setRsa( 'system', '*', [ 'GET' ] );
        $this->setRsa( 'system', 'app', [ 'POST' ] );

        $this->assertEquals( VerbsMask::GET_MASK, $this->check( 'system' ), 'check(system)' );
        $this->assertEquals( VerbsMask::GET_MASK, $this->check( 'system', 'role' ), 'check(system, role)' );
        $this->assertEquals( VerbsMask::POST_MASK, $this->check( 'system', 'app' ), 'check(system, app)' );
    }

    public function testMostSpecificOverwrite2()
    {
        $this->setRsa( 'system', 'app', [ 'GET' ] );
        $this->setRsa( 'system', 'app/*', [ 'POST' ] );

        $this->assertEquals( VerbsMask::GET_MASK, $this->check( 'system', 'app' ), 'check(system, app)' );
        $this->assertEquals( VerbsMask::POST_MASK, $this->check( 'system', 'app/1' ), 'check(system, app/1)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'system', 'role' ), 'check(system, role)' );
    }

    public function testMultiVerb()
    {
        $verbs = [ 'GET', 'POST', 'PUT', 'PATCH' ];
        $this->setRsa( 'system', 'role', $verbs );

        $this->assertEquals( VerbsMask::arrayToMask( $verbs ), $this->check( 'system', 'role' ), 'check(system, role)' );
        $this->assertEquals( VerbsMask::NONE_MASK, $this->check( 'system', 'role/1' ), 'check(system, role/1)' );
    }

    public function testMultiVerb2()
    {
        $this->setRsa( 'system', 'role', [ 'GET' ] );
        $this->setRsa( 'system', 'role', [ 'POST' ] );
        $this->setRsa( 'system', 'role', [ 'PATCH' ] );

        $this->assertEquals( VerbsMask::arrayToMask( [ 'GET', 'POST', 'PATCH' ] ), $this->check( 'system', 'role' ), 'check(system, role)' );
    }

    public function testRequestor()
    {
        $this->setRsa( 'system', 'role', [ 'GET' ] );
        $this->setRsa( 'system', 'role', [ 'POST' ], ServiceRequestorTypes::SCRIPT );
        $this->setRsa( 'system', 'role', [ 'PATCH' ] );

        $this->assertEquals( VerbsMask::arrayToMask( [ 'GET', 'PATCH' ] ), $this->check( 'system', 'role' ), 'check(system, role)' );
        $this->assertEquals( VerbsMask::POST_MASK, $this->check( 'system', 'role', ServiceRequestorTypes::SCRIPT ), 'check(system, role, script)' );
    }

    public function testRequestor2()
    {
        $this->setRsa( 'system', 'role', [ 'GET' ] );
        $this->setRsa( 'system', 'role', [ 'POST' ], ServiceRequestorTypes::SCRIPT );
        $this->setRsa( 'system', 'role', [ 'PATCH' ] );
        $this->setRsa( 'system', 'role', [ 'DELETE' ], ( ServiceRequestorTypes::API | ServiceRequestorTypes::SCRIPT ) );

        $this->assertEquals( VerbsMask::arrayToMask( [ 'GET', 'PATCH', 'DELETE' ] ), $this->check( 'system', 'role' ), 'check(system, role)' );
        $this->assertEquals(
            VerbsMask::arrayToMask( [ 'POST', 'DELETE' ] ),
            $this->check( 'system', 'role', ServiceRequestorTypes::SCRIPT ),
            'check(system, role, script)'
        );
    }

    protected function setRsa( $service, $component = null, $verbs = [ 'GET', 'POST', 'PUT', 'PATCH', 'DELETE' ], $requestor = ServiceRequestorTypes::API )
    {
        $verbMask = VerbsMask::arrayToMask( $verbs );

        $rsa = [
            'service'        => $service,
            'component'      => $component,
            'verb_mask'      => $verbMask,
            'requestor_mask' => $requestor
        ];

        $this->rsa[] = $rsa;

        Session::setCurrentApiKey($this->apiKey);
        Session::putWithApiKey( $this->apiKey, $this->rsaKey, $this->rsa );
    }

    protected function check( $service, $component = null, $requestor = ServiceRequestorTypes::API )
    {
        return Session::getServicePermissions( $service, $component, $requestor );
    }
}