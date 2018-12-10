<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;

use App\Account;
use App\User;
use App\Note;
use App\Profile;
use App\PGPkey;
use App\Secret;
use App\Group;
use App\GroupUser;
use Hash;
use App\Mail\SendMailable;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        return view('page.dashboard');
    }
    // public function getUserAccounts()
    // {

    //     $accounts = DB::table('accounts')
    //         ->join('secrets', 'accounts.id', '=', 'secrets.account_id')
    //         ->where('secrets.user_id', '=', Auth::user()->id)
    //         ->select('accounts.*','secrets.data')
    //         ->get();
        
    //     return $accounts;
    // }
    // public function getUserNotes()
    // {
        // $notes = DB::table('notes')
    //     //     ->join('secrets', 'notes.id', '=', 'secrets.note_id')
    //     //     ->where('secrets.user_id', '=', Auth::user()->id)
    //     //     ->select('notes.*')
    //     //     ->get();
    //     $notes = Auth::user()->note()->get();
        
    //     return $notes;
    // }
    public function accounts()
    {
        $accounts = Auth::user()->account()->get();         
        return view('page.accounts', compact('accounts'));
    }

    public function addAccount(Request $request)
    {
        $account = new Account;
        $account->name = $request->name;
        $account->uri = $request->url;
        $account->username = $request->username;
        $account->description = $request->description;
        $account->save();

        // Nối id tới secret ứng mỗi user khác nhau
        $secret = new Secret;
        $user = Auth::user();
        $secret->user_id = $user->id;
        $secret->account_id = $account->id;
        // TODO: encrypt OpenGPG
        $secret->data = $request->cipher;
        $secret->save();
        
        $accounts = Auth::user()->account()->get();
        return response()->json([
            'success' => true,
            // TODO: lang this message
            'message' => 'Thêm tài khoản thành công.',
            'view' => view('content.content-accounts', compact('accounts'))->render()
        ]);
    }

    public function editAccount(Request $request){
        $account_id = $request->id;
        $acc = Account::find($account_id);
        $acc->name = $request->name;
        $acc->username = $request->username;
        $acc->uri = $request->url;
        $acc->description = $request->description;
        $acc->save();

        if ($request->cipher) {
            $secret = Secret::where('account_id', $account_id)->first();
            $secret->data = $request->cipher;
            $secret->save();
        }
        
        $accounts = Auth::user()->account()->get();
        return response()->json([
            'success' => true,
            // TODO: lang this message
            'message' => 'Chỉnh sửa tài khoản thành công.',
            'view' => view('content.content-accounts', compact('accounts'))->render()
        ]);
    }

    public function deleteAccount(Request $request){
        $account_id = $request->id;
        
        $acc = Account::find($account_id);
        $acc->delete();

        $secret = Secret::where('account_id', $account_id)->first();
        $secret->delete();

        $accounts = Auth::user()->account()->get();
        return response()->json([
            'success' => true,
            // TODO: lang this message
            'message' => 'Xóa tài khoản thành công.',
            'view' => view('content.content-accounts', compact('accounts'))->render()
        ]);
    }
    
    public function shareAccount(Request $request){
        $account_id = $request->idShare;
        $acc = Account::find($account_id);
        //TODO: share account
    }

    public function getPassword(Request $request)
    {
        $account_id = $request->id;
        $secret = Secret::where('account_id', $account_id)->first();

        return response()->json([
            'success' => true,
            'message' => 'Đã sao chép mật khẩu',
            'content' => $secret->data
        ]);
    }

    public function notes()
    {
        $notes = Auth::user()->note()->get();

        return view('page.notes',compact('notes'));
    }

    public function getNoteContent(Request $request)
    {
        $note_id = $request->id;
        $secret = Secret::where('note_id', $note_id)->first();

        return response()->json([
            'success' => true,
            'message' => 'Đã sao chép nội dung',
            'content' => $secret->data
        ]);
    }


    public function addNote( Request $req)
    {
        $note = new Note();
        $note->title = $req->title;
        $note->save();

        // Nối id tới secret ứng mỗi user khác nhau
        $secret = new Secret;
        $user = Auth::user();
        $secret->user_id = $user->id;
        $secret->note_id = $note->id;
        // TODO: encrypt OpenGPG
        $secret->data = $req->cipher;
        $secret->save();

        $notes = Auth::user()->note()->get();
        return response()->json([
            'success' => true,
            // TODO: lang this message
            'message' => 'Thêm ghi chú bảo mật thành công.',
            'view' => view('content.content-notes', compact('notes'))->render()
        ]);
    }

    public function editNote( Request $req)
    {
        $note_id = $req->id;
        $note = Note::find($note_id);
        $note->title = $req->title;
        $note->save();

        if ($request->cipher) {
            $secret = Secret::where('note_id', $note_id)->first();
            $secret->data = $request->cipher;
            $secret->save();
        }

        $notes = Auth::user()->note()->get();
        return response()->json([
            'success' => true,
            // TODO: lang this message
            'message' => 'Chỉnh sửa ghi chú bảo mật thành công.',
            'view' => view('content.content-notes', compact('notes'))->render()
        ]);
    }

    public function delNote(Request $req){
        $note_id = $req->id;
        $note = Note::find($note_id);
        $note->delete();

        $secret = Secret::where('note_id', $note_id)->first();
        $secret->delete();

        $notes = Auth::user()->note()->get();
        return response()->json([
            'success' => true,
            // TODO: lang this message
            'message' => 'Xóa ghi chú bảo mật thành công.',
            'view' => view('content.content-notes', compact('notes'))->render()
        ]);
    }

    public function shareNote(Request $request){
        $idShare = $request->idShare;
        $acc = Account::find($idShare);
        //TODO: share note
    }

    public function drive()
    {
        $allFiles = Storage::disk('userstorage')->allFiles(Auth::user()->id);

        $files = array();

        foreach ($allFiles as $file) {

            $files[] = $this->fileInfo(pathinfo(storage_path('app/store/').$file));
        }
       
        return view('page.drive', compact('files'));
        
    }
    public function fileInfo($filePath)
    {
        $file = array();
        $file['name'] = $filePath['filename'];
        $file['extension'] = $filePath['extension'];
        $file['size'] = filesize($filePath['dirname'] . '/' . $filePath['basename']);
        $file['lastModified'] = date("d/m/Y", filemtime($filePath['dirname'] . '/' . $filePath['basename']));
        return $file;
    }
    
    public function addFile(Request $request)
    {
        $path = $request->file('fileToUpload')->storeAs(
            'store/'.Auth::user()->id, $request->file('fileToUpload')->getClientOriginalName()
        );

        $allFiles = Storage::disk('userstorage')->allFiles(Auth::user()->id);

        $files = array();

        foreach ($allFiles as $file) {

            $files[] = $this->fileInfo(pathinfo(storage_path('app/store/').$file));
        }
       
        return response()->json([
            'success' => true,
            // TODO: lang this message
            'message' => 'Thêm ghi chú bảo mật thành công.',
            'view' => view('content.content-drive', compact('files'))->render()
        ]);
        // $path=$request->)->store('store');
        // echo $path;
    }
    public function delFile(Request $request)
    {
        $filename  = $request->filename;
        Storage::disk('userstorage')->delete(Auth::user()->id.'/'.$filename);

        $allFiles = Storage::disk('userstorage')->allFiles(Auth::user()->id);

        $files = array();

        foreach ($allFiles as $file) {

            $files[] = $this->fileInfo(pathinfo(storage_path('app/store/').$file));
        }
       
        return response()->json([
            'success' => true,
            // TODO: lang this message
            'message' => 'Xóa tài liệu thành công.',
            'view' => view('content.content-drive', compact('files'))->render()
        ]);
    }

    public function downloadFile(Request $request)
    {
        $filename  = $request->filename;
        return Storage::disk('userstorage')->download(Auth::user()->id.'/'.$filename);
    }

    public function sendMail()
    {
        $data = array('name'=>"Ngan", "body" => "Test mail");
    
        Mail::send('emails.sendmail', $data, function($message) {
            $message->to('dangthingan1996@gmail.com', 'Ngan Dang')
                    ->subject('Web Testing Mail');
            $message->from('ngandt52@gmail.com','SecPass');
        });
        return "Your email has been sent successfully";
    }
    
    public function credential()
    {
        return view('page.credential');
    }
    
    public function settings()
    {
        return view('page.settings', compact());
    }
    public function sharewith()
    {
        return view('page.sharewith');
    }


    public function groups()
    {
        $groups = Auth::user()->group()->get();
        return view('page.groups', compact('groups'));
    }
    public function groupDetail(Request $request)
    {
        $group_id = $request->get('id');
        $group = Group::find($group_id);
        $groups_users = GroupUser::where('group_id', $group_id)->get();
        
        $user_id = $groups_users->pluck('user_id');
       
        $users = User::whereIn('id',$user_id)->get();
        return view('page.groupdetail', compact('users', 'groups_users','group'));
        // return view('page.groupdetail');
    }
    public function checkUser(Request $request)
    {
        $email = $request->email;
        $user = User::where('email',$request->email)->first();
        if($user != null)
        {
            return response()->json([
                'success' => true,
                // TODO: lang this message
                'message' => 'Người dùng tồn tại.'
            ]);
        }
        
        else{
            return response()->json([
                'success' => false,
                // TODO: lang this message
                'message' => 'Người dùng không tồn tại.'
            ],500);
        }
    }
    public function addGroup(Request $request)
    {
        $user_admin = Auth::user();
        $group = new Group;
        $group->name = $request->name;
        $group->created_by = $user_admin->name;
        $group->modified_by = $user_admin->name;
        $group->save();

        $group_user = new GroupUser;
        $group_user->group_id = $group->id;
        $group_user->user_id = $user_admin->id;
        $group_user->is_admin = true;
        $group_user->save();
       
        $data = json_decode(stripslashes($_POST['li_variable']));

        foreach($data as $d){
            $user = User::where('email',$d)->first();
            $group_user = new GroupUser;
            $group_user->group_id = $group->id;
            $group_user->user_id = $user->id;
            $group_user->save();
        }
        
        // $users = User::where('email',$request->email)->get();
        // foreach ($list as $email)
        // {
        //     $user = User::where('email',$email)->first();

        //     $group_user = new GroupUser;
        //     $group_user->group_id = $group->id;
        //     $group_user->user_id = $user->id;
        //     $group_user->save();
        // }
        $groups = Auth::user()->group()->get();
        return response()->json([
            'success' => true,
            // TODO: lang this message
            'message' => 'Tạo nhóm mới thành công.',
            'view' => view('content.content-group', compact('groups'))->render()
        ]);
    }
    
    public function editGroup(Request $request)
    {
        $group_id = $request->id;
        $group = Group::find($group_id);
        $group->name = $request->name;
        $group->created_by = $user_admin->name;
        $group->modified_by = $user_admin->name;
        $group->save();

    }

    public function deleteGroup(Request $request)
    {
        $group_id = $request->id;
        
        $group = Group::find($group_id);
        $group->delete();

        $group_user = GroupUser::where('group_id', $group_id)->first();
        $group_user->delete();

        $groups = Auth::user()->group()->get();
        return response()->json([
            'success' => true,
            // TODO: lang this message
            'message' => 'Xóa tài khoản thành công.',
            'view' => view('content.content-group', compact('groups'))->render()
        ]);
    }
    

    public function profile()
    { 
        $user = Auth::user();
        return view('page.profile', compact('user'));
    }

    public function quickSearch(Request $request)
    { 
        $user = Auth::user();
        return view('content.quicksearch', compact('user'));
    } 

    public function pgp()
    {
        return view('page.pgp');
    }

    public function addPGP()
    {
        // TODO: receiving Info and publicKey from addon.
        $pgp_key = new PGPkey;
        $pgp_key->user_id = Auth::user()->id;
        $pgp_key->armored_key = "abc";
        $pgp_key->uid = "Nguyen Phi Cuong (cuong@secpass.com)";
        $pgp_key->key_id = "ABCDDBCA";
        $pgp_key->fingerprint = "ABCDDBCAABCDDBCAABCDDBCAABCDDBCA";
        $pgp_key->type = "what is this??";
        $pgp_key->expires = NOW();
        $pgp_key->key_created = NOW();
        $pgp_key->save();
        
        return $pgp_key;
    }

    public function keepalive()
    {
        return response('',204);
    }

}
