import {IsNumber, IsOptional, IsString, ValidateNested} from "class-validator";
import {Expose} from "class-transformer";

export class AppConfig {
  @Expose()
  @IsString()
  optionsApiUrl: string;

  @Expose()
  @IsNumber()
  sceneId: number;

  @Expose()
  @ValidateNested()
  userConfig?: UserConfig = {};
}

export class UserConfig {
  @Expose()
  @IsString()
  @IsOptional()
  userId?: string;

  @Expose()
  @IsString()
  @IsOptional()
  sessionId?: string;
}
