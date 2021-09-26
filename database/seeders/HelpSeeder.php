<?php

namespace Database\Seeders;

use App\Models\Help;
use Illuminate\Database\Seeder;

class HelpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *php artisan krlove:generate:model
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'question' => 'What is the refund policy?',
                'answer' => '
                    <p>Spotify terms and conditions specifically bar the user / payment to a curator for guaranteed placement. That is why networks like us and similar ones are designed to charge users based on review and feedback ONLY, with giving curators the option to easily add a song if they like it. This is stated in the terms and conditions that you agree to before purchasing.</p>
                    <p><a href="https://uplaylist-static.s3.us-east-2.amazonaws.com/terms-conditions.pdf">Terms & Conditions here</a></p>
                    <p>We hope you understand as this is a standard practice for legitimate networks. Anyone who promises guaranteed placement can put your account at risk and Spotify bans accounts who participate in this behavior and can even remove any streams they gained you. Our playlisting system, although frustrating because it is not guaranteed, is one of the only ways for artists to safely secure placement without jeopardizing their account.</p>
                ',
            ],
            [
                'question' => "What is 'Placement Percentage'?",
                'answer' => '
                    <h3 class="h3">Each playlist has their own percentage of how many songs make it onto the playlist out of the # of tracks submitted.</h3>
                    <p>The placement percentage of a playlist is how risky it is to submit your music to that playlist.</p>
                    <p>For example, if the percentage for a playlist is 10%, that means that they place 10 songs per 100 submissions.</p>
                    <p>Just as a reminder, we recommend submitting to playlists with varying placement percentages and that align to your music\'s genre so that you have a better chance of getting your track placed!</p>
                    <p>Happy pitching!</p>
                ',
            ],
            [
                'question' => 'How do I use this website?',
                'answer' => '
                    <h3 class="h3">If you want to reach new listeners + increase your streams, playlisting is 100% the tool to get you there! Submit your music to Spotify playlists with just a few steps.</h3>
                    <p><strong>How to submit to playlists:</strong></p>
                    <ol>
                        <li><strong>Go to the</strong> <a href="https://uplaylist.com/">UPlaylist</a> website.</li>
                        <li><strong>View the different playlists</strong> and add them to your cart that you want to apply to.</li>
                        <li><strong>Click the cart icon</strong> and press "Checkout."</li>
                        <li><strong>Enter your basic information</strong> such as name and email</li>
                        <li><strong>Enter your song details</strong> including: Track Name, Spotify Link, and Genre.</li>
                        <li><strong>Checkout and Pay</strong> Your music will be sent to the curator for review. <strong>Curators have up to 4 days to review and provide feedback to your submission. It can then take up to a week for the Curator to add the song if it\'s approved, because Curators have their own process/timeline of adding new music to their playlists.</strong></li>
                    </ol>
                    <p><strong>PLACEMENT NOT GUARANTEED</strong> because this is the only ways for artists to safely secure placement without jeopardizing their Spotify account</p>
                ',
            ],
            [
                'question' => 'How do I list my playlists?',
                'answer' => '
                    <h3 class="h3">Start getting stellar song submissions from uPlaylist with just a few steps!</h3>
                    <p>First, there are a couple qualifications before getting started:</p>
                    <ol>
                        <li>Your Spotify playlist must <strong>100+ followers</strong></li>
                        <li>Your playlists must not be exclusive to a certain type of artist or song. For example, if you have a playlist for songs only attributed with a certain label, please do not add these to the AR playlisting network.</li>
                    </ol>
                    <p>How to add Playlists:</p>
                    <ol>
                        <li>Go to the <a href="https://uplaylist.com/">UPlaylist</a> website and click "Become a Curator."</li>
                        <li>Register with your basic information.</li>
                        <li>Login and go to the "Playlists" tab.</li>
                        <li>Connect your Spotify account.</li>
                        <li>Select which playlists you would like to list along along with a price.</li>
                    </ol>
                ',
                'vendor' => 1,
            ],
            [
                'question' => 'Curator vs Curator PRO',
                'answer' => '
                    <h3 class="h3">So you are interested in/ already listing yourself as a Curator on the Playlisting network? Here\'s how to decide if a Curator PRO plan might be right for you!</h3>
                    <p>The <strong>Pro Subscription</strong> allows curators to enhance their experience by:</p>
                    <ul>
                        <li>boosting your listing through a verified check mark added to all playlists</li>
                        <li>higher return on investment as the fee is only 15% of your listing price instead of 50%</li>
                        <li>start listing at $5/ playlist instead of $1</li>
                        <li>4 days to review a submission instead of 2</li>
                    </ul>
                    <p>To be a Curator Pro costs $10 a month! So for example, a regular curator who receives 20 orders in a month with a $5 submission price will make $50. A PRO curator that lists their playlists for the same price and also receives 20 orders will make $75 that month, after paying their $10 subscription.</p>
                    <p>To get started login to uPlaylist and head to the "Pro" tab on the left navbar.</p>
                    <p>Happy curating!</p>
                ',
                'vendor' => 1,
            ],
        ];

        foreach ($data as $row) {
            Help::updateOrCreate(['question' => $row['question']], $row);
        }
    }
}
