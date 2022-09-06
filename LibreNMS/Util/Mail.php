<?php
/*
 * Mail.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use Exception;
use LibreNMS\Config;
use LibreNMS\Exceptions\RrdGraphException;
use PHPMailer\PHPMailer\PHPMailer;

class Mail
{
    /**
     * Parse string with emails. Return array with email (as key) and name (as value)
     *
     * @param  string  $emails
     * @return array|false
     */
    public static function parseEmails($emails)
    {
        $result = [];
        $regex = '/^[\"\']?([^\"\']+)[\"\']?\s{0,}<([^@]+@[^>]+)>$/';
        if (is_string($emails)) {
            $emails = preg_split('/[,;]\s{0,}/', $emails);
            foreach ($emails as $email) {
                if (preg_match($regex, $email, $out, PREG_OFFSET_CAPTURE)) {
                    $result[$out[2][0]] = $out[1][0];
                } else {
                    if (strpos($email, '@')) {
                        $from_name = Config::get('email_user');
                        $result[$email] = $from_name;
                    }
                }
            }

            return $result;
        }

        // Return FALSE if input not string
        return false;
    }

    /**
     * Send email with PHPMailer
     *
     * @param  string  $emails
     * @param  string  $subject
     * @param  string  $message
     * @param  bool  $html
     * @return bool|string
     */
    public static function send($emails, $subject, $message, bool $html = false, ?bool $embedGraphs = null)
    {
        if (is_array($emails) || ($emails = self::parseEmails($emails))) {
            d_echo("Attempting to email $subject to: " . implode('; ', array_keys($emails)) . PHP_EOL);
            $mail = new PHPMailer(true);
            try {
                $mail->Hostname = php_uname('n');

                foreach (self::parseEmails(Config::get('email_from')) as $from => $from_name) {
                    $mail->setFrom($from, $from_name);
                }
                foreach ($emails as $email => $email_name) {
                    $mail->addAddress($email, $email_name);
                }
                $mail->Subject = $subject;
                $mail->XMailer = Config::get('project_name');
                $mail->CharSet = 'utf-8';
                $mail->WordWrap = 76;
                $mail->Body = $message;
                if ($embedGraphs ?? Config::get('email_attach_graphs')) {
                    self::embedGraphs($mail);
                }
                if ($html) {
                    $mail->isHTML();
                }
                switch (strtolower(trim(Config::get('email_backend')))) {
                    case 'sendmail':
                        $mail->Mailer = 'sendmail';
                        $mail->Sendmail = Config::get('email_sendmail_path');
                        break;
                    case 'smtp':
                        $mail->isSMTP();
                        $mail->Host = Config::get('email_smtp_host');
                        $mail->Timeout = Config::get('email_smtp_timeout');
                        $mail->SMTPAuth = Config::get('email_smtp_auth');
                        $mail->SMTPSecure = Config::get('email_smtp_secure');
                        $mail->Port = Config::get('email_smtp_port');
                        $mail->Username = Config::get('email_smtp_username');
                        $mail->Password = Config::get('email_smtp_password');
                        $mail->SMTPAutoTLS = Config::get('email_auto_tls');
                        $mail->SMTPDebug = 0;
                        break;
                    default:
                        $mail->Mailer = 'mail';
                        break;
                }

                return $mail->send();
            } catch (\PHPMailer\PHPMailer\Exception $e) {
                return $e->errorMessage();
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }

        return 'No contacts found';
    }

    /**
     * Search for generated graph links, generate them, attach them to the email and update the url to a cid link
     */
    private static function embedGraphs(PHPMailer $mail): void
    {
        $body = $mail->Body;

        // search for generated graphs
        preg_match_all('/ class=\"librenms-graph\" src=\"(.*?)\"/', $body, $match);

        foreach (array_values(array_unique($match[1])) as $attachment_id => $url) {
            try {
                $cid = "graph$attachment_id";

                // fetch image, do not debug as it will return the wrong format.
                $prev = Debug::isEnabled();
                Debug::set(false);
                $image = Graph::get(Url::parseLegacyPathVars($url));
                Debug::set($prev);

                // attach image
                if (Config::get('webui.graph_type') == 'svg') {
                    $mail->addStringEmbeddedImage($image, $cid, "$cid.svg", PHPMailer::ENCODING_BASE64, 'image/svg+xml');
                } else {
                    $mail->addStringEmbeddedImage($image, $cid, "$cid.png", PHPMailer::ENCODING_BASE64, 'image/png');
                }

                // update image tag to link to attached image
                $body = str_replace($url, "cid:$cid", $body);
            } catch (RrdGraphException|\PHPMailer\PHPMailer\Exception $e) {
                report($e);
            }
        }

        $mail->Body = $body;
    }
}
