<?php
// code : by rama
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SSOController extends Controller
{
    // membuat format header
    function json_rpc_header($userid, $password)
    {
        date_default_timezone_set("UTC");
        $inttime = strval(time() - strtotime("1970-01-01 00:00:00"));
        $value = $userid . "&" . $inttime;
        $key = $password;
        $signature = hash_hmac("sha256", $value, $key, true);
        $signature64 = base64_encode($signature);
        $headers =
            [
                "userid:" . $userid,
                "signature:" . $signature64,
                "key:" . $inttime
            ];
        return $headers;
    }

    public function login()
    {
        $user = 'user';
        $pwd = 'password';
        $header = $this->json_rpc_header($user, $pwd);
        $urlTo = "https://sso-bsw.kotabogor.go.id/oulsso/websvc.php";

        // route do login, jika user berhasil melakukan login maka akan diarahkan ke route do login.
        $url_do_login = route('login.dologin');

        // jika ingin menggunakan default login bisa dikosonkan
        $url_form_login = "";

        $aplikasi = 'Nama Aplikasi';
        $data = 'data=
      	   {
      		 "jsonrpc": "2.0",
      		 "method": "get_sso_token",
      		 "params":
        		 {
        		   "php_is_native":"1",
        		   "url_do_login":"' . $url_do_login . '",
        		   "url_form_login":"' . $url_form_login . '",
        		   "nama_aplikasi":"' . $aplikasi . '"
        		 }
      	   }
      	';

        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $urlTo);
        curl_setopt($c, CURLOPT_POST, TRUE);
        curl_setopt($c, CURLOPT_HTTPHEADER, $header);
        curl_setopt($c, CURLOPT_POSTFIELDS, $data);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($c, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);

        $res = curl_exec($c);
        if (curl_errno($c)) {
            echo 'Curl error: ' . curl_error($c);
        }

        curl_close($c);
        $json = json_decode($res);
        $status = $json->status;
        $message = $json->result->message;
        $token = $json->result->token;

        if ($status == 'sukses') {
            $token = $json->result->token;

            // redirect to https://sso-bsw.kotabogor.go.id/oulsso/app/do_sso/ + token
            return redirect('https://sso-bsw.kotabogor.go.id/oulsso/app/do_sso/' . $token);
        } else {
            redirect('https://sso-bsw.kotabogor.go.id/oulsso/app/login' . rawurlencode($message));
        }
    }

    public function dologin($sso_otp = null)
    {
        date_default_timezone_set("Asia/Bangkok");

        $sso_otp = $_GET['otp_sso'];

        $user = 'user';
        $pwd = 'password';
        $header = $this->json_rpc_header($user, $pwd);
        $urlTo = "https://sso-bsw.kotabogor.go.id/oulsso/websvc.php";

        // mengkonfirmasi kode otp yang sudah didapatkan dari fungsi login.
        $aplikasi = 'Nama Aplikasi';
        $data = 'data=
        		{
        		  "jsonrpc": "2.0",
        		  "method": "do_sso_login",
        		  "params":
          		  {
          		    "otp_sso":"' . $sso_otp . '",
          		    "nama_aplikasi":"e-SPPT"
          		  }
        		}
        ';

        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $urlTo);
        curl_setopt($c, CURLOPT_POST, TRUE);
        curl_setopt($c, CURLOPT_HTTPHEADER, $header);
        curl_setopt($c, CURLOPT_POSTFIELDS, $data);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($c, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);

        $res = curl_exec($c);
        if (curl_errno($c)) {
            echo 'Curl error: ' . curl_error($c);
        }
        curl_close($c);

        $json = json_decode($res);
        $status = $json->status;
        $message = $json->result->message;

        if ($status != 'sukses') //jika gagal;
        {
            exit;
            abort(403, 'Anda tidak memiliki hak mengakses laman tersebut!');
        } else {
            $user = User::where('email', $json->result->email)->first();
            if ($user != null) {
                if (Auth::login($user)) {
                    return redirect()->route('home');
                } else {
                    session()->flash('error', 'Mohon maaf email SSO tidak sama!.');
                    return redirect()->route('login');
                }
            }
            session()->flash('error', 'Mohon maaf email SSO tidak sama!.');
            return redirect()->route('login');
        }
    }
}
