<?php

namespace App\Http\Controllers;

use App\AdditionalUser;
use App\City;
use App\Mail\Confremiation;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.users.index')->with('users',User::withoutBanned()->get());
    }
    public function paindUser()
    {
        return view('admin.users.index')->with('users',User::onlyBanned()->get());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show_notify($id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
            return redirect($notification->data['url']);
        }
    }
    public function user()
    {
        return view('admin.users.index')->with('users',User::withoutBanned()->where('type','user')->get());
    }
    public function famous()
    {
        return view('admin.users.index')->with('users',User::withoutBanned()->where('type','famous')->get());
    }
    public function verfy_account(Request $request){
        $user = User::find($request->id);
        $user->verify = $request->status;
        $user->save();
        if($user->verify == 1){
            Mail::to($user->email)->send(new Confremiation($user));
        }
    }
    public function create()
    {
        return view('admin.users.create')->with('citys',City::get());
    }
    public function panUser($id){
        $user = User::find($id);
        $user->ban();
        return redirect()->back()->with(['success'=>'تم حظر العضو بنجاح']);
    }
    public function unpanUser($id){
        $user = User::find($id);
        $user->unban();
        // dd($user->isNotBanned());
        return redirect()->back()->with(['success'=>'تم إلغاء حظر العضو بنجاح']);
    }

    

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'email'=>'required|email|unique:users,email',
            'phone'=>'required|unique:users,phone',
            'name'=>'required',
            'type'=>'required',
            'password'=>'required',
            'gender'=>'required',
            'address_id'=>'required'
        ]);
        $request_all = $request->except(['password','type']);
        $request_all['type']=$request->type;
        $request_all['password'] = bcrypt($request->password);
        $request_all['image'] = 'user/deflut.png';

      $user =   User::create($request_all);
      $more = new AdditionalUser();
      $more->user_id = $user->id;
      $more->save();
        return redirect()->route('users.index')->with(['success'=>'تم الإضافة بنجاح']);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return view('admin.users.show')->with('user',User::with('moreInfo')->find($id));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('admin.users.edit')->with('user',User::find($id))->with('citys',City::get());

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        $request->validate([
            'email'=>'required|email|unique:users,email,'.$user->id,
            'phone'=>'required|unique:users,phone,'.$user->id,
            'name'=>'required',
            'type'=>'required',
            'gender'=>'required',
            'address_id'=>'required'
        ]);
        $request_all = $request->except(['password','type']);
        $request_all['type']=$request->type;
        if($request->password != null){
            $request_all['password'] = bcrypt($request->password);
        }
        if($request->image != null){
            $request_all['image'] = $request->image->store('user');
        }

        $user->update($request_all);
        if($user->type == 'famous'){
            $add = AdditionalUser::where('user_id',$id)->first();
            if(!$add){
                $more = new AdditionalUser();
                $more->user_id = $user->id;
                $more->save();
            }
        }
        
    
        return redirect()->route('users.index')->with(['success'=>'تم الإضافة بنجاح']);

    }
 
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        User::find($id)->delete();
        return redirect()->back()->with(['success'=>'تم الحذف بنجاح']);

    }
}
