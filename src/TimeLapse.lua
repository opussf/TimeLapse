TIMELAPSE_MSG_ADDONNAME = "TimeLapse";
TIMELAPSE_MSG_VERSION   = GetAddOnMetadata(INEED_MSG_ADDONNAME,"Version");
TIMELAPSE_MSG_AUTHOR    = "opussf";

-- Colours
COLOR_RED = "|cffff0000";
COLOR_GREEN = "|cff00ff00";
COLOR_BLUE = "|cff0000ff";
COLOR_PURPLE = "|cff700090";
COLOR_YELLOW = "|cffffff00";
COLOR_ORANGE = "|cffff6d00";
COLOR_GREY = "|cff808080";
COLOR_GOLD = "|cffcfb52b";
COLOR_NEON_BLUE = "|cff4d4dff";
COLOR_END = "|r";

TL_Options = { ["Enabled"] = 1, ["Delay"] = 60, ["Debug"] = 1, }
TL = {}
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
end
--------------
function TL.ADDON_LOADED()
	-- Unregister the event for this method.
	TIMELAPSE_Frame:UnregisterEvent("ADDON_LOADED")

	TL.Print("Loaded: "..time())
	TL.Print("I am "..(TL_Options.Enabled and "Enabled" or "Disabled")..".")
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
	local cmd, param = TL.parseCmd(msg);
	local cmdFunc = TL.CommandList[cmd];
	if cmdFunc then
		cmdFunc.func(param);
	else
		TL.PrintHelp()
	end
end
function TL.PrintHelp()
	TL.Print(TIMELAPSE_MSG_ADDONNAME.." by "..TIMELAPSE_MSG_AUTHOR);
	for cmd, info in pairs(TL.CommandList) do
		TL.Print(string.format("%s %s %s -> %s",
			SLASH_TIMELAPSE1, cmd, info.help[1], info.help[2]));
	end
end
function TL.Status()
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
		["func"] = function() TL_Options.Debug = not TL_Options.Debug; TL_Print("Debug is "..(TL_Options.Debug and "On" or "Off")); end,
		["help"] = {"","Toggles the debug status"},
	},
}

