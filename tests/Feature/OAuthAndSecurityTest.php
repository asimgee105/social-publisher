<?php

namespace Tests\Feature;

use App\Models\SocialAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OAuthAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_social_account_tokens_are_encrypted(): void
    {
        $rawToken = 'secret_access_token_12345';

        $account = SocialAccount::create([
            'platform' => 'instagram',
            'account_name' => '@test_account',
            'access_token' => $rawToken,
            'status' => 'active',
        ]);

        // Verify Eloquent decrypts on retrieval
        $this->assertEquals($rawToken, $account->access_token);

        // Verify database raw value is NOT plain text
        $dbRaw = \DB::table('social_accounts')->where('id', $account->id)->value('access_token');
        $this->assertNotEquals($rawToken, $dbRaw);

        // Verify array export hides secret tokens
        $arrayData = $account->toArray();
        $this->assertArrayNotHasKey('access_token', $arrayData);
        $this->assertArrayNotHasKey('refresh_token', $arrayData);
    }
}
