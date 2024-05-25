<?php

declare(strict_types=1);

/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D Community Edition is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D Community Edition
 *
 * @author     HDVinnie <hdinnovations@protonmail.com>
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 */

use App\Http\Requests\StoreTorrentRequest;

beforeEach(function (): void {
    $this->subject = new StoreTorrentRequest();
});

test('authorize', function (): void {
    $actual = $this->subject->authorize();

    expect($actual)->toBeTrue();
});

test('rules', function (): void {
    $this->markTestIncomplete('This test case was generated by Shift. When you are ready, remove this line and complete this test case.');

    $actual = $this->subject->rules();

    $this->assertValidationRules([
        'torrent' => [
            'required',
            'file',
            'mimes:torrent',
            'mimetypes:application/x-bittorrent',
        ],
        'name' => [
            'required',
            'unique:torrents',
            'max:255',
        ],
        'description' => [
            'required',
            'max:2097152',
        ],
        'mediainfo' => [
            'nullable',
            'sometimes',
            'max:2097152',
        ],
        'bdinfo' => [
            'nullable',
            'sometimes',
            'max:2097152',
        ],
        'category_id' => [
            'required',
            'exists:categories,id',
        ],
        'type_id' => [
            'required',
            'exists:types,id',
        ],
        'resolution_id' => [
            'exists:resolutions,id',
        ],
        'region_id' => [
            'nullable',
            'exists:regions,id',
        ],
        'distributor_id' => [
            'nullable',
            'exists:distributors,id',
        ],
        'imdb' => [
        ],
        'tvdb' => [
        ],
        'tmdb' => [
        ],
        'mal' => [
        ],
        'igdb' => [
        ],
        'season_number' => [
        ],
        'episode_number' => [
        ],
        'anon' => [
            'required',
            'boolean',
        ],
        'stream' => [
            'required',
            'boolean',
        ],
        'sd' => [
            'required',
            'boolean',
        ],
        'personal_release' => [
            'required',
            'boolean',
        ],
        'internal' => [
            'sometimes',
            'boolean',
        ],
        'free' => [
            'sometimes',
            'integer',
            'numeric',
            'between:0,100',
        ],
        'refundable' => [
            'sometimes',
            'boolean',
        ],
    ], $actual);
});

test('messages', function (): void {
    $actual = $this->subject->messages();

    expect($actual)->toEqual([
        'igdb.in' => "The IGBB ID must be 0 if the media doesn't exist on IGDB or you're not uploading a game.",
        'tmdb.in' => "The TMDB ID must be 0 if the media doesn't exist on TMDB or you're not uploading a tv show or movie.",
        'imdb.in' => "The IMDB ID must be 0 if the media doesn't exist on IMDB or you're not uploading a tv show or movie.",
        'tvdb.in' => "The TVDB ID must be 0 if the media doesn't exist on TVDB or you're not uploading a tv show.",
        'mal.in'  => "The MAL ID must be 0 if the media doesn't exist on MAL or you're not uploading a tv or movie.",
    ]);
});
