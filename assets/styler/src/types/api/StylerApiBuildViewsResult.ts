import {Expose, Type} from "class-transformer";
import {IsArray, IsNotEmpty, IsString, ValidateNested} from "class-validator";

export class StylerApiBuildViewsResult {
  @Type(() => StylerApiBuildViewsResultView)
  @Expose(({name: "files"}))
  @IsArray()
  @IsNotEmpty()
  @ValidateNested()
  views: StylerApiBuildViewsResultView[] = [];
}

export class StylerApiBuildViewsResultView {
  @Expose({name: "view_code"})
  @IsNotEmpty()
  @IsString()
  code: string;

  @Expose()
  @IsNotEmpty()
  @IsString()
  uri: string;
}
