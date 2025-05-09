<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use phpseclib3\Crypt\Hash; 
use phpseclib3\Crypt\RSA;
use Storage;
use Fpdf\Fpdf as Fpdf;
use SetaPDF_Core;
use SetaPDF_Core_Document;
use setasign\Fpdi\Tcpdf\Fpdi;
use setasign\Fpdi\TcpdfFpdi;
use TCPDF;
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

    public function makePdf(Request $request){
        $content = $request['pdf_content'];
        $pdf = new Fpdf();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',16);
        $pdf->cell(40,10,$content);
        return response($pdf->Output('S'))->header('Content-Type', 'application/pdf');
    }

    public function viewPdf(){
        return view('pdf');
    }
    
    public function hash(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf|max:2048', // Validate file type
        ]);
    
        // Get the uploaded file
        $file = $request->file('file');
    
        // Store the file with a fixed name (test.pdf)
        $filePath = $file->storeAs('uploads', 'test.pdf');
        $fullPath = storage_path('app/' . $filePath);
    
        // Ensure the certificate file exists
        $certificatePath = 'file://'.storage_path('app/test.crt');
        if (!file_exists($certificatePath)) {
            return response()->json(['error' => 'Certificate file not found'], 404);
        }
        // Create a new TCPDF instance with FPDI
        $pdf = new Fpdi();
    
        // Disable header and footer (keeps original PDF as is)
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
    
        // Set the source PDF file
        $pageCount = $pdf->setSourceFile($fullPath);
    
        // Import all pages without modifying them
        for ($i = 1; $i <= $pageCount; $i++) {
            $tplIdx = $pdf->importPage($i);
            $pdf->AddPage();
            $pdf->useTemplate($tplIdx, 0, 0, 210, 297); // Full page
        }
    
        // Digital Signature Information
        $info = [
            'Name' => 'TCPDF',
            'Location' => 'Office',
            'Reason' => 'Testing Digital Signature',
            'ContactInfo' => 'http://www.tcpdf.org',
        ];
        // Add digital signature
        $pdf->setSignature($certificatePath, $certificatePath, 'tcpdfdemo', '', 2, $info);
        $pdf->SetFont('helvetica', '', 12);

        // add a page
        $pdf->AddPage();

        // print a line of text
        $text = 'This is a <b color="#FF0000">digitally signed document</b> using the <b>miracle.crt</b> certificate.<br /></a>';
        $pdf->writeHTML($text, true, 0, true, 0);
        $pdf->Output('example_052.pdf', 'D');
 
    }
    

    public function HashPdf(Request $request){
        // create a writer
        // create a new document instance
        $document = SetaPDF_Core_Document::loadByFilename(
            'files/pdfs/etown/Laboratory-Report.pdf', $writer
        );

        // create a signer instance
        $signer = new \SetaPDF_Signer($document);

        // set some signature properties
        $signer->setReason('Testing PAdES module');
        $signer->setLocation('SetaPDF-Signer Manual');

        // We need more space, if the signature will include a timestamp signature
        $signer->setSignatureContentLength(18000);

        // create a signature module
        $module = new \SetaPDF_Signer_Signature_Module_Pades();
        // load the certificate
        $certificate = 'file://files/certificates/setapdf-no-pw.pem';
        $module->setCertificate($certificate);
        $module->setPrivateKey(array($certificate, '' /* no password */));

        // create a timestamp module
        $tsModule = new \SetaPDF_Signer_Timestamp_Module_Rfc3161_Curl();
        $tsModule->setUrl('http://zeitstempel.dfn.de');
        $signer->setTimestampModule($tsModule);

        // sign the document and send the final document to the initial writer
        $signer->sign($module);
    }
}
