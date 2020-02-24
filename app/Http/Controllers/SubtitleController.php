<?php
/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D Community Edition is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D Community Edition
 *
 * @author     Howard Lau <howardlau1999@hotmail.com>
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 */

namespace App\Http\Controllers;

use App\Models\Subtitle;
use Illuminate\Http\Request;

class SubtitleController
{
    /**
     * Download A Subtitle.
     *
     * @param Request $request
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(Request $request, $id)
    {
        $subtitle = Subtitle::withAnyStatus()->findOrFail($id);
        $subtitle->increment('hits', 1);

        $filePath = getcwd().'/files/subtitles/'.$subtitle->torrent_id.'/'.$subtitle->id.'.'.$subtitle->ext;
        // The subtitle file exist ?
        if (!file_exists($filePath)) {
            return redirect()->route('torrent', ['id' => $subtitle->torrent_id])
                ->withErrors('Subtitle File Not Found! Please Report This Torrent!');
        }

        return response()->download($filePath, $subtitle->file_name);
    }
}
