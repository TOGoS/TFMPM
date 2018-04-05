CREATE TABLE map_generation (
	generation_id text, -- probably a uuid
	tfmpm_commit_id text,
	generator_node_name text, -- e.g. togos-fbs
	-- generation parameters:
	factorio_commit_id text,
	data_commit_id text,
	map_seed integer,
	map_scale integer,
	map_width integer, -- Also the height!
	map_offset_x integer,
	map_offset_y integer,
	slope_shading real,
	-- generation info:
	generation_start_time timestamp,
	generation_end_time timestamp,
	compilation_reported_elapsed_time real, -- number of seconds generator compilation took, according to factorio
	generation_reported_elapsed_time real, -- number of seconds generation took
	-- outputs:
	map_image_urn text,
	log_file_urn text,
	PRIMARY KEY ( generation_id )
);

CREATE TABLE resource_stats (
       generation_id text,
       resource_name text,
       total_quantity real,
       average_quantity real,
       max_unclamped_probability real,
       max_richness real,
       average_richness real,
       PRIMARY KEY ( generation_id, resource_name )
);
