TIMELAPSE_SLUG, TL = ...
TIMELAPSE_MSG_ADDONNAME = C_AddOns.GetAddOnMetadata( TIMELAPSE_SLUG, "Title" )
TIMELAPSE_MSG_VERSION   = C_AddOns.GetAddOnMetadata( TIMELAPSE_SLUG, "Version" )
TIMELAPSE_MSG_AUTHOR    = C_AddOns.GetAddOnMetadata( TIMELAPSE_SLUG, "Author" )

-- Colours
COLOR_RED = "|cffff0000"
COLOR_GREEN = "|cff00ff00"
COLOR_BLUE = "|cff0000ff"
COLOR_PURPLE = "|cff700090"
COLOR_YELLOW = "|cffffff00"
COLOR_ORANGE = "|cffff6d00"
COLOR_GREY = "|cff808080"
COLOR_GOLD = "|cffcfb52b"
COLOR_NEON_BLUE = "|cff4d4dff"
COLOR_END = "|r"

TL_Options = { ["Enabled"] = 1, ["Delay"] = 60, ["Debug"] = nil, }
TL.LastCapture = 0

function TL.Print( msg, showName)
	-- print to the chat frame
	-- set showName to false to suppress the addon name printing
	if (showName == nil) or (showName) then
		msg = COLOR_PURPLE..TIMELAPSE_MSG_ADDONNAME.."> "..COLOR_END..msg
	end
	DEFAULT_CHAT_FRAME:AddMessage( msg )
end
function TL.OnLoad()
	SLASH_TIMELAPSE1 = "/TIMELAPSE"
	SLASH_TIMELAPSE2 = "/TL"
	SlashCmdList["TIMELAPSE"] = function(msg) TL.command(msg); end
	TIMELAPSE_Frame:RegisterEvent("ADDON_LOADED")
	TIMELAPSE_Frame:RegisterEvent("SCREENSHOT_SUCCEEDED")
	TIMELAPSE_Frame:RegisterEvent("SCREENSHOT_FAILED")
end
--------------
function TL.ADDON_LOADED( _, arg1 )
	if( arg1 == TIMELAPSE_SLUG ) then
		-- Unregister the event for this method.
		TIMELAPSE_Frame:UnregisterEvent("ADDON_LOADED")

		TL.Print(TIMELAPSE_MSG_VERSION.." Loaded: "..time())
		TL.Status()
	end
end
function TL.SCREENSHOT_FAILED()
end
function TL.SCREENSHOT_SUCCEEDED()
end
function TL.OnUpdate()
	if TL_Options.Enabled then
		if time() >= TL.LastCapture + TL_Options.Delay then -- Capture an image
			TL.LastCapture = time()
			Screenshot()
			if TL_Options.Debug then
				TL.Print("Captured a screenshot")
			end
		end
	end
end
-- Non Event functions
function TL.parseCmd(msg)
	if msg then
		msg = string.lower(msg)
		local a,b,c = strfind(msg, "(%S+)")  --contiguous string of non-space characters
		if a then
			-- c is the matched string, strsub is everything after that, skipping the space
			return c, strsub(msg, b+2)
		else
			return ""
		end
	end
end
function TL.command(msg)
	local cmd, param = TL.parseCmd(msg)
	if TL.CommandList[cmd] and TL.CommandList[cmd].alias then
		cmd = TL.CommandList[cmd].alias
	end
	local cmdFunc = TL.CommandList[cmd]
	if cmdFunc then
		cmdFunc.func(param)
	else
		cmd = tonumber(cmd)
		if cmd then
			TL.CommandList.enable.func( tonumber(cmd) )
		else
			TL.Status()
			TL.PrintHelp()
		end
	end
end
function TL.PrintHelp()
	TL.Print( string.format( "%s (%s) by %s", TIMELAPSE_MSG_ADDONNAME, TIMELAPSE_MSG_VERSION, TIMELAPSE_MSG_AUTHOR ) )
	for cmd, info in pairs(TL.CommandList) do
		if info.help then
			local cmdStr = cmd
			for c2, i2 in pairs(TL.CommandList) do
				if i2.alias and i2.alias == cmd then
					cmdStr = string.format( "%s / %s", cmdStr, c2 )
				end
			end
			TL.Print(string.format("%s %s %s -> %s",
				SLASH_TIMELAPSE1, cmdStr, info.help[1], info.help[2]))
		end
	end
end
function TL.Status()
	TL.Print( "Screen Capture every "..SecondsToTime(TL_Options.Delay,false,false,5).." ("..( TL_Options.Enabled and "Enabled" or "Disabled" )..")" )
end
-- this needs to be at the end because it is referencing functions
TL.CommandList = {
	["help"] = {
		["func"] = TL.PrintHelp,
		["help"] = {"","Print this help."},
	},
	["status"] = {
		["func"] = TL.Status,
		["help"] = {"","Show Status"},
	},
	["debug"] = {
		["func"] = function() TL_Options.Debug = not TL_Options.Debug; TL.Print("Debug is "..(TL_Options.Debug and "On" or "Off")); end,
		["help"] = {"","Toggles the debug status"},
	},
	["disable"] = {
		["func"] = function() TL_Options.Enabled = nil; TL.Status(); end,
		["help"] = {"","Disable taking screenshots"},
	},
	["enable"] = {
		["func"] = function(param) param = tonumber(param); TL_Options.Enabled = 1; if param then if param<=0 then param=1 end;	TL_Options.Delay = param; end; TL.Status(); end,
		["help"] = {"<seconds>","Enable taking screenshots. Optionally set delay."},
	},
	["off"] = {
		["alias"] = "disable",
	},
	["on"] = {
		["alias"] = "enable",
	},
	["delay"] = {
		["func"] = function(param) param = tonumber(param); if param then if param<=0 then param=1 end;	TL_Options.Delay = param; TL_Options.Enabled = 1; end; TL.Status(); end,
		["help"] = {"<seconds>","Set the capture delay to number of seconds. Setting a value enables, no value just reports."}
	},
}

