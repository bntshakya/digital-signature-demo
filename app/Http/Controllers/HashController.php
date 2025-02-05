<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use phpseclib3\Crypt\Hash; 
use phpseclib3\Crypt\RSA;
use Storage;

class HashController extends Controller
{
    private $pvtKey;
    private $cipherText;
    // public function getHash(){
    //     $hash = new Hash();
    //     $hashmsg = bin2hex($hash->hash('test'));
    //     $private = RSA::createKey();
    //     $public = $private->getPublicKey();
    //     // dd($private->toString('PKCS8'),$public);
    //     $signature = $private->sign($hashmsg);
    //     dd($signature,$private->getPublicKey()->encrypt($hashmsg));
    //     echo $public->verify('acsdf',$signature)?'true':'false';
    //     dd('ee');
    //     return response()->json(['hash'=>bin2hex($hash->hash('test'))]);
    // }

    public function generateKeys(){
        $this->pvtKey = RSA::createKey();
        $privateKeypem = $this->pvtKey->toString('PKCS8');
        $publicKeypem = $this->pvtKey->getPublicKey()->toString('PKCS8');
        Storage::disk('local')->put('private_key.pem', $privateKeypem);
        Storage::disk('local')->put('public_key.pem', $publicKeypem);
        return response()->json(['msg'=>'key generated']);
    }

    public function encrypt(Request $request) {
        $contractmsg = Storage::disk('public')->get('contract.txt');
        $hash = new Hash();
        $hashmsg = bin2hex($hash->hash($contractmsg));
        $publicKeyPem = Storage::disk('local')->get('public_key.pem');
        $publicKey = RSA::loadPublicKey($publicKeyPem);
        $this->cipherText = $publicKey->encrypt($hashmsg);
        session(['cipherText'=>($this->cipherText)]);
        return response()->json(['ciphertext'=>bin2hex($this->cipherText),'hash'=>$hashmsg]);
    }

    public function decrypt(Request $request){
        $this->cipherText = session('cipherText');
        $privateKeyPem = Storage::disk('local')->get('private_key.pem');
        $privateKey = RSA::loadPrivateKey($privateKeyPem);
        $decryptedMsgHash = $privateKey->decrypt($this->cipherText);
        $receivedMsg = Storage::disk('public')->get('contract.txt');
        $hashatreceiver = new Hash();
        $hashatreceivermsg = bin2hex($hashatreceiver->hash($receivedMsg));
        $msg = $hashatreceivermsg == $decryptedMsgHash?'hash match':'hash dont match';
        return response()->json(['plaintext'=>$hashatreceivermsg,'decryptedMsgHash'=>$decryptedMsgHash,'msg'=>$msg]);
    }
}
