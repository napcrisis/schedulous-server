<?php

class LoginsController extends BaseController {

	/**
	 * Display a listing of logins
	 *
	 * @return Response
	 */
	public function index()
	{
		$logins = Login::all();

		return View::make('logins.index', compact('logins'));
	}

	/**
	 * Show the form for creating a new login
	 *
	 * @return Response
	 */
	public function create()
	{
		return View::make('logins.create');
	}

	/**
	 * Store a newly created login in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$validator = Validator::make($data = Input::all(), Login::$rules);

		if ($validator->fails())
		{
			return Redirect::back()->withErrors($validator)->withInput();
		}

		Login::create($data);

		return Redirect::route('logins.index');
	}

	/**
	 * Display the specified login.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		$login = Login::findOrFail($id);

		return View::make('logins.show', compact('login'));
	}

	/**
	 * Show the form for editing the specified login.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$login = Login::find($id);

		return View::make('logins.edit', compact('login'));
	}

	/**
	 * Update the specified login in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$login = Login::findOrFail($id);

		$validator = Validator::make($data = Input::all(), Login::$rules);

		if ($validator->fails())
		{
			return Redirect::back()->withErrors($validator)->withInput();
		}

		$login->update($data);

		return Redirect::route('logins.index');
	}

	/**
	 * Remove the specified login from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		Login::destroy($id);

		return Redirect::route('logins.index');
	}

}
